<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Controller;

use Fusonic\HttpKernelBundle\Attribute\FromRequest;
use Fusonic\HttpKernelBundle\ConstraintViolation\ArgumentCountConstraintViolation;
use Fusonic\HttpKernelBundle\ConstraintViolation\MissingConstructorArgumentsConstraintViolation;
use Fusonic\HttpKernelBundle\ConstraintViolation\NotNormalizableValueConstraintViolation;
use Fusonic\HttpKernelBundle\ConstraintViolation\TypeConstraintViolation;
use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Normalizer\ConstraintViolationExceptionNormalizer;
use Fusonic\HttpKernelBundle\Normalizer\DecoratedBackedEnumNormalizer;
use Fusonic\HttpKernelBundle\Provider\ContextAwareProviderInterface;
use Fusonic\HttpKernelBundle\Request\StrictRequestDataCollector;
use Fusonic\HttpKernelBundle\Tests\Dto\ArrayDto;
use Fusonic\HttpKernelBundle\Tests\Dto\DummyClassA;
use Fusonic\HttpKernelBundle\Tests\Dto\EmptyDto;
use Fusonic\HttpKernelBundle\Tests\Dto\EnumDto;
use Fusonic\HttpKernelBundle\Tests\Dto\ExampleEnum;
use Fusonic\HttpKernelBundle\Tests\Dto\IntArrayDto;
use Fusonic\HttpKernelBundle\Tests\Dto\NestedDto;
use Fusonic\HttpKernelBundle\Tests\Dto\NotADto;
use Fusonic\HttpKernelBundle\Tests\Dto\RouteParameterDto;
use Fusonic\HttpKernelBundle\Tests\Dto\StringIdDto;
use Fusonic\HttpKernelBundle\Tests\Dto\TestDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestDtoResolverTest extends TestCase
{
    public function testSupportOfNotSupportedClass(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 15]]);
        $argument = $this->createArgumentMetadata(NotADto::class, []);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testResolveOfNotSupportedClass(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 5]]);
        $argument = $this->createArgumentMetadata(NotADto::class, []);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testSupportOfNotExistingClass(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 5]]);
        $argument = $this->createArgumentMetadata('NotExistingClass', [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testSupportWithNull(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 5]]);
        $argument = new ArgumentMetadata('routeParameterDto', null, false, false, null);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testValidation(): void
    {
        $this->expectException(ConstraintViolationException::class);

        /** @var string $data */
        $data = json_encode(
            [
                'float' => 9.99,
                'bool' => true,
            ]
        );

        $request = new Request([], [], [], [], [], [], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $iterable = $resolver->resolve($request, $argument);

        $dto = $iterable->current();
        self::assertInstanceOf(TestDto::class, $dto);
    }

    public function testExpectedFloatProvidedIntStrictTypeChecking(): void
    {
        /** @var string $data */
        $data = json_encode([
            'int' => 5,
            'float' => 9,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ], \JSON_THROW_ON_ERROR);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(TestDto::class, $dto);
        self::assertSame(9.0, $dto->getFloat());
    }

    public function testStrictTypeMappingForPostJsonRequestBody(): void
    {
        /** @var string $data */
        $data = json_encode([
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ], \JSON_THROW_ON_ERROR);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(TestDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertTrue($dto->isBool());

        self::assertSame('barfoo', $dto->getSubType()->getTest());
    }

    public function testStrictTypeMappingForPostFormRequestBody(): void
    {
        $data = [
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ];

        $request = new Request([], $data, [], [], [], []);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(TestDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertTrue($dto->isBool());

        self::assertSame('barfoo', $dto->getSubType()->getTest());
    }

    public function testValidEnumFormRequestBody(): void
    {
        $data = [
            'exampleEnum' => 'CHOICE_1',
        ];

        $request = new Request([], $data, [], [], [], []);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();

        self::assertInstanceOf(EnumDto::class, $dto);
        self::assertSame(ExampleEnum::CHOICE_1, $dto->exampleEnum);
    }

    public function testInvalidEnumFormRequestBody(): void
    {
        $data = [
            'exampleEnum' => 'WRONG_CHOICE',
        ];

        $request = new Request([], $data, [], [], [], []);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage(
            'The value you selected is not a valid choice.'
        );

        $generator->current();
    }

    public function testInvalidEnumFormQuery(): void
    {
        $request = new Request([
            'exampleEnum' => 'WRONG_CHOICE',
        ]);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage(
            'The value you selected is not a valid choice.'
        );

        $generator->current();
    }

    public function testInvalidEnumTypeFormBody(): void
    {
        $data = [
            'exampleEnum' => [],
        ];

        $request = new Request([], $data, [], [], [], []);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage(
            'ConstraintViolation: This value should be of type int|string.'
        );
        $generator->current();
    }

    public function testSkippingBodyGetRequest(): void
    {
        $this->expectException(ConstraintViolationException::class);

        /** @var string $data */
        $data = json_encode([
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ], \JSON_THROW_ON_ERROR);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $generator->current();
    }

    public function testInvalidRequestBodyHandling(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $data = [
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
        ];
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data).'foobar');
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);
        $generator->current();
    }

    public function testDuplicateKeyHandling(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $query = [
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
        ];
        $attributes = [
            '_route_params' => [
                'int' => 5,
                'float' => 9.99,
                'string' => 'foobar',
                'bool' => true,
            ],
        ];

        $request = new Request($query, [], $attributes);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $generator->current();
    }

    public function testQueryParameterHandling(): void
    {
        $query = [
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(TestDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertTrue($dto->isBool());
    }

    public function testInvalidQueryParameterHandling(): void
    {
        $this->expectException(ConstraintViolationException::class);

        $query = [
            'int' => [
                'subentity' => [1, 2, 3, 4],
            ],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $generator->current();
    }

    public function testRouteParameterHandlingWithNotMatchingTypes(): void
    {
        $attributes = [
            '_route_params' => [
                'int' => 5,
                'float' => 9.99,
                'string' => 'foobar',
                'bool' => true,
            ],
        ];
        $request = new Request([], [], $attributes);
        $argument = $this->createArgumentMetadata(RouteParameterDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(RouteParameterDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame('9.99', $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertSame(1, $dto->isBool());
    }

    public function testRouteParameterHandlingWithStrings(): void
    {
        $attributes = [
            '_route_params' => [
                'int' => '5',
                'float' => '9.99',
                'string' => 'foobar',
                'bool' => true,
            ],
        ];
        $request = new Request([], [], $attributes);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(RouteParameterDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(RouteParameterDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame('9.99', $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertSame(1, $dto->isBool());
    }

    public function testInvalidTypeMappingHandling(): void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage(
            'ConstraintViolation: This value should be of type float.'
        );
        /** @var string $data */
        $data = json_encode([
            'int' => 5,
            'float' => 'foobar',
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ], \JSON_THROW_ON_ERROR);
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);
        $generator->current();
    }

    public function testEmptyBodyHandling(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(EmptyDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(EmptyDto::class, $dto);
    }

    public function testContextAwareProviderCalling(): void
    {
        /** @var string $data */
        $data = json_encode([
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ], \JSON_THROW_ON_ERROR);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $mockProvider1 = $this->createMock(ContextAwareProviderInterface::class);
        $mockProvider1->expects(self::once())->method('supports')->willReturn(true);
        $mockProvider1->expects(self::once())->method('provide');

        $mockProvider2 = $this->createMock(ContextAwareProviderInterface::class);
        $mockProvider2->expects(self::once())->method('supports')->willReturn(false);
        $mockProvider2->expects(self::never())->method('provide');

        $providers = [$mockProvider1, $mockProvider2];

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator(), null, $providers);
        $resolver->resolve($request, $argument)->current();
    }

    /**
     * @param array<mixed> $data
     * @param class-string $dtoClass
     * @param class-string $expectedViolationClass
     *
     * @dataProvider errorTestData
     */
    public function testConstraintViolationErrors(array $data, string $dtoClass, string $expectedViolationClass): void
    {
        /** @var string $data */
        $data = json_encode($data, \JSON_THROW_ON_ERROR);
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);

        $argument = $this->createArgumentMetadata($dtoClass, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $exception = null;
        try {
            $generator->current();
        } catch (\Throwable $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertInstanceOf(ConstraintViolationException::class, $exception);
        $violations = $exception->getConstraintViolationList();

        self::assertCount(1, $violations);
        self::assertInstanceOf($expectedViolationClass, $violations->get(0));
    }

    public function testTypeError(): void
    {
        $request = new Request(['requiredArgument' => null]);
        $request->setMethod(Request::METHOD_GET);

        $argument = $this->createArgumentMetadata(DummyClassA::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $exception = null;
        try {
            $generator->current();
        } catch (\Throwable $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertInstanceOf(ConstraintViolationException::class, $exception);
        $violations = $exception->getConstraintViolationList();

        self::assertCount(1, $violations);
        self::assertInstanceOf(TypeConstraintViolation::class, $violations->get(0));
    }

    public function testIntegerRouteParameterTypeError(): void
    {
        $request = new Request([], ['requiredArgument' => '1']);
        $request->setMethod(Request::METHOD_POST);

        $argument = $this->createArgumentMetadata(DummyClassA::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver(
            $this->getDenormalizer(),
            $this->getValidator(),
            null,
            [],
            new StrictRequestDataCollector(false)
        );
        $generator = $resolver->resolve($request, $argument);

        $this->expectExceptionMessage('ConstraintViolation: This value should be of type int.');

        /* @var DummyClassA $dto */
        $generator->current();
    }

    public function testValidIntegerRouteParameter(): void
    {
        $request = new Request([], ['requiredArgument' => 1]);
        $request->setMethod(Request::METHOD_POST);

        $argument = $this->createArgumentMetadata(DummyClassA::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver(
            $this->getDenormalizer(),
            $this->getValidator(),
            null,
            [],
            new StrictRequestDataCollector(false)
        );
        $generator = $resolver->resolve($request, $argument);

        /* @var DummyClassA $dto */
        $dto = $generator->current();

        self::assertSame(1, $dto->getRequiredArgument());
    }

    public function testInvalidValueForNotForcingRouteParamIntegers(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 1]]);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(StringIdDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver(
            $this->getDenormalizer(),
            $this->getValidator(),
            null,
            [],
            new StrictRequestDataCollector(false)
        );

        $this->expectException(ConstraintViolationException::class);
        $iterable = $resolver->resolve($request, $argument);

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessage(
            'ConstraintViolation: This value should be of type string.'
        );

        $iterable->current();
    }

    public function testValidValueForNotForcingRouteParamIntegers(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => '1']]);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(StringIdDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver(
            $this->getDenormalizer(),
            $this->getValidator(),
            null,
            [],
            new StrictRequestDataCollector(false)
        );

        $iterable = $resolver->resolve($request, $argument);

        $dto = $iterable->current();

        self::assertInstanceOf(StringIdDto::class, $dto);
        self::assertSame('1', $dto->id);
    }

    /**
     * @return array<array<mixed>>
     */
    public function errorTestData(): array
    {
        return [
            [
                [],
                DummyClassA::class,
                ArgumentCountConstraintViolation::class,
            ],
            [
                ['requiredArgument' => 'test'],
                DummyClassA::class,
                NotNormalizableValueConstraintViolation::class,
            ],
            [
                ['nonExistingArgument' => 1],
                DummyClassA::class,
                MissingConstructorArgumentsConstraintViolation::class,
            ],
            [
                ['requiredArgument' => 1, 'items' => null],
                ArrayDto::class,
                NotNormalizableValueConstraintViolation::class,
            ],
            [
                ['items' => null],
                IntArrayDto::class,
                NotNormalizableValueConstraintViolation::class,
            ],
            [
                ['objectArgument' => ['requiredArgument' => null]],
                NestedDto::class,
                NotNormalizableValueConstraintViolation::class,
            ],
            [
                ['objectArgument' => null],
                NestedDto::class,
                MissingConstructorArgumentsConstraintViolation::class,
            ],
        ];
    }

    private function getDenormalizer(): DenormalizerInterface
    {
        $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $encoders = [new JsonEncoder()];
        $constraintViolationListNormalizer = new ConstraintViolationListNormalizer();
        $normalizers = [
            new UnwrappingDenormalizer(),
            new ConstraintViolationExceptionNormalizer($constraintViolationListNormalizer),
            new DecoratedBackedEnumNormalizer(new BackedEnumNormalizer()),
            new ProblemNormalizer(),
            new UidNormalizer(),
            new JsonSerializableNormalizer(),
            $constraintViolationListNormalizer,
            new DateTimeZoneNormalizer(),
            new DateTimeNormalizer(),
            new DateIntervalNormalizer(),
            new DataUriNormalizer(),
            new BackedEnumNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer(null, null, null, $extractor),
        ];

        return new Serializer($normalizers, $encoders);
    }

    /**
     * @param array<mixed> $arguments
     */
    private function createArgumentMetadata(string $class, array $arguments): ArgumentMetadata
    {
        return new ArgumentMetadata('dto', $class, false, false, null, false, $arguments);
    }

    private function getValidator(): ValidatorInterface
    {
        $validatorBuilder = Validation::createValidatorBuilder();

        // @phpstan-ignore-next-line
        Kernel::VERSION_ID >= 70000 ? $validatorBuilder->enableAttributeMapping() : $validatorBuilder->enableAnnotationMapping();

        return $validatorBuilder->getValidator();
    }
}
