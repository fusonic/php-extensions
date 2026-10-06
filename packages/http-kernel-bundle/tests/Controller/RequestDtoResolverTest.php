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
use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Provider\ContextAwareProviderInterface;
use Fusonic\HttpKernelBundle\Request\RequestDataCollector;
use Fusonic\HttpKernelBundle\Tests\Dto\ArrayDto;
use Fusonic\HttpKernelBundle\Tests\Dto\DateTimeDto;
use Fusonic\HttpKernelBundle\Tests\Dto\DummyClassA;
use Fusonic\HttpKernelBundle\Tests\Dto\EmptyDto;
use Fusonic\HttpKernelBundle\Tests\Dto\EnumDto;
use Fusonic\HttpKernelBundle\Tests\Dto\ExampleStringBackedEnum;
use Fusonic\HttpKernelBundle\Tests\Dto\IntArrayDto;
use Fusonic\HttpKernelBundle\Tests\Dto\NestedDto;
use Fusonic\HttpKernelBundle\Tests\Dto\NotADto;
use Fusonic\HttpKernelBundle\Tests\Dto\QueryDtoWithAttribute;
use Fusonic\HttpKernelBundle\Tests\Dto\RouteParameterDto;
use Fusonic\HttpKernelBundle\Tests\Dto\StringIdDto;
use Fusonic\HttpKernelBundle\Tests\Dto\TestDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class RequestDtoResolverTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testSupportOfNotSupportedClass(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 15]]);
        $argument = $this->createArgumentMetadata(NotADto::class, []);

        $resolver = $this->getRequestDtoResolver();
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testResolveOfNotSupportedClass(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 5]]);
        $argument = $this->createArgumentMetadata(NotADto::class, []);

        $resolver = $this->getRequestDtoResolver();
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testSupportOfNotExistingClass(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 5]]);
        $argument = $this->createArgumentMetadata('NotExistingClass', [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testSupportWithNull(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 5]]);
        $argument = new ArgumentMetadata('routeParameterDto', null, false, false, null);

        $resolver = $this->getRequestDtoResolver();
        self::assertNull($resolver->resolve($request, $argument)->current());
    }

    public function testValidation(): void
    {
        $this->expectException(ConstraintViolationException::class);

        $data = $this->encodeToJson([
            'float' => 9.99,
            'bool' => true,
        ]);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $iterable = $resolver->resolve($request, $argument);

        $dto = $iterable->current();
        self::assertInstanceOf(TestDto::class, $dto);
    }

    public function testExpectedFloatProvidedIntStrictTypeChecking(): void
    {
        $data = json_encode(
            value: [
                'int' => 5,
                'float' => 9,
                'string' => 'foobar',
                'bool' => true,
                'subType' => [
                    'test' => 'barfoo',
                ],
            ],
            flags: \JSON_THROW_ON_ERROR
        );

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(TestDto::class, $dto);
        self::assertSame(9.0, $dto->getFloat());
    }

    public function testStrictTypeMappingForPostJsonRequestBody(): void
    {
        $data = $this->encodeToJson([
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ]);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
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

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(TestDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertTrue($dto->isBool());

        self::assertSame('barfoo', $dto->getSubType()->getTest());
    }

    public function testStringValuesInFormRequestBody(): void
    {
        $data = [
            'int' => '5',
            'float' => '9.99',
            'string' => 'foobar',
            'bool' => 'true',
            'subType' => [
                'test' => 'barfoo',
            ],
        ];

        $request = new Request([], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $dto = $this->getRequestDtoResolver()->resolve($request, $argument)->current();

        self::assertInstanceOf(TestDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertTrue($dto->isBool());
        self::assertSame('barfoo', $dto->getSubType()->getTest());
    }

    public function testInvalidStringValueInFormRequestBody(): void
    {
        $data = [
            'int' => 'invalid',
            'float' => '9.99',
            'string' => 'foobar',
            'bool' => 'true',
            'subType' => [
                'test' => 'barfoo',
            ],
        ];

        $request = new Request([], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $ex = null;

        try {
            $this->getRequestDtoResolver()->resolve($request, $argument)->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);
        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);
        self::assertSame('int', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('invalid', $constraintViolationList->get(0)->getInvalidValue());
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
        self::assertSame(ExampleStringBackedEnum::CHOICE_1, $dto->exampleEnum);
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
        $this->expectExceptionMessageIs('ConstraintViolation: The value you selected is not a valid choice.');

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
        $this->expectExceptionMessageIs('ConstraintViolation: The value you selected is not a valid choice.');

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
        $this->expectExceptionMessageIs('ConstraintViolation: The value you selected is not a valid choice.');
        $generator->current();
    }

    public function testSkippingBodyGetRequest(): void
    {
        $this->expectException(ConstraintViolationException::class);

        $data = $this->encodeToJson([
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ]);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
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

        $resolver = $this->getRequestDtoResolver();
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

        $resolver = $this->getRequestDtoResolver();
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

        $resolver = $this->getRequestDtoResolver();
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

        $resolver = $this->getRequestDtoResolver();
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

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(RouteParameterDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertTrue($dto->isBool());
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

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(RouteParameterDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('foobar', $dto->getString());
        self::assertTrue($dto->isBool());
    }

    public function testInvalidTypeMappingHandling(): void
    {
        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessageIs(
            'ConstraintViolation: This value should be of type float.'
        );

        $data = $this->encodeToJson([
            'int' => 5,
            'float' => 'foobar',
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ]);

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(TestDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);
        $generator->current();
    }

    public function testEmptyBodyHandling(): void
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(EmptyDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(EmptyDto::class, $dto);
    }

    public function testContextAwareProviderCalling(): void
    {
        $data = $this->encodeToJson([
            'int' => 5,
            'float' => 9.99,
            'string' => 'foobar',
            'bool' => true,
            'subType' => [
                'test' => 'barfoo',
            ],
        ]);

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

        $resolver = new RequestDtoResolver(
            serializer: $this->getDenormalizer(),
            validator: $this->getValidator(),
            providers: $providers,
        );
        $resolver->resolve($request, $argument)->current();
    }

    /**
     * @param array<mixed> $data
     * @param class-string $dtoClass
     * @param class-string $expectedViolationClass
     */
    #[DataProvider('errorTestData')]
    public function testConstraintViolationErrors(array $data, string $dtoClass, string $expectedViolationClass): void
    {
        $data = $this->encodeToJson($data);
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);

        $argument = $this->createArgumentMetadata($dtoClass, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
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

        $resolver = $this->getRequestDtoResolver();
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
        self::assertInstanceOf(NotNormalizableValueConstraintViolation::class, $violations->get(0));
        self::assertSame('null', $violations->get(0)->getInvalidValue());
        self::assertSame('requiredArgument', $violations->get(0)->getPropertyPath());
    }

    public function testUrlParsingError(): void
    {
        $request = new Request(['requiredArgument' => 'aaaa']);
        $request->setMethod(Request::METHOD_GET);

        $argument = $this->createArgumentMetadata(DummyClassA::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
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
        self::assertInstanceOf(NotNormalizableValueConstraintViolation::class, $violations->get(0));
        self::assertSame('aaaa', $violations->get(0)->getInvalidValue());
        self::assertSame('requiredArgument', $violations->get(0)->getPropertyPath());
    }

    public function testIntegerRouteParameterTypeError(): void
    {
        $request = new Request([], ['requiredArgument' => '1']);
        $request->setMethod(Request::METHOD_POST);

        $argument = $this->createArgumentMetadata(DummyClassA::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver(
            serializer: $this->getDenormalizer(),
            validator: $this->getValidator(),
            requestDataCollector: new RequestDataCollector(false),
        );
        $generator = $resolver->resolve($request, $argument);

        $this->expectExceptionMessageIs('ConstraintViolation: This value should be of type int.');

        /* @var DummyClassA $dto */
        $generator->current();
    }

    public function testValidIntegerRouteParameter(): void
    {
        $request = new Request([], ['requiredArgument' => 1]);
        $request->setMethod(Request::METHOD_POST);

        $argument = $this->createArgumentMetadata(DummyClassA::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver(
            serializer: $this->getDenormalizer(),
            validator: $this->getValidator(),
            requestDataCollector: new RequestDataCollector(false),
        );
        $generator = $resolver->resolve($request, $argument);

        /* @var DummyClassA $dto */
        $dto = $generator->current();

        self::assertSame(1, $dto->getRequiredArgument());
    }

    public function testArrayQueryParameterForScalarProperty(): void
    {
        $request = new Request(['int' => ['5'], 'string' => 'foo']);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(QueryDtoWithAttribute::class, []);

        $ex = null;

        try {
            $this->getRequestDtoResolver()->resolve($request, $argument)->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);
        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);
        self::assertSame('int', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('[]', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testDateTimeQueryParameterHandling(): void
    {
        $request = new Request(['date' => '2024-01-11T10:00:00+00:00', 'dates' => ['2024-01-12T10:00:00+00:00']]);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(DateTimeDto::class, [new FromRequest()]);

        $dto = $this->getRequestDtoResolver()->resolve($request, $argument)->current();

        self::assertInstanceOf(DateTimeDto::class, $dto);
        self::assertSame('2024-01-11', $dto->date->format('Y-m-d'));
        self::assertSame('2024-01-12', $dto->dates[0]->format('Y-m-d'));
    }

    public function testInvalidValueForNotForcingRouteParamIntegers(): void
    {
        $request = new Request([], [], ['_route_params' => ['id' => 1]]);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(StringIdDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver(
            serializer: $this->getDenormalizer(),
            validator: $this->getValidator(),
            providers: [],
            requestDataCollector: new RequestDataCollector(false),
        );

        $this->expectException(ConstraintViolationException::class);
        $iterable = $resolver->resolve($request, $argument);

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessageIs(
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
            serializer: $this->getDenormalizer(),
            validator: $this->getValidator(),
            providers: [],
            requestDataCollector: new RequestDataCollector(false),
        );

        $iterable = $resolver->resolve($request, $argument);

        $dto = $iterable->current();

        self::assertInstanceOf(StringIdDto::class, $dto);
        self::assertSame('1', $dto->id);
    }

    public function testDefaultQueryParameterHandling(): void
    {
        $request = new Request(['int' => '5', 'float' => '9.99', 'string' => '1', 'bool' => '1']);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(QueryDtoWithAttribute::class, []);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $dto = $resolver->resolve($request, $argument)->current();

        self::assertInstanceOf(QueryDtoWithAttribute::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame(9.99, $dto->getFloat());
        self::assertSame('1', $dto->getString());
    }

    public function testDefaultRouteParameterHandlingWithRequestBody(): void
    {
        $request = new Request([], [], ['_route_params' => ['int' => '5', 'string' => 'foo']], [], [], ['CONTENT_TYPE' => 'application/json'], '{}');
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(RouteParameterDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $dto = $resolver->resolve($request, $argument)->current();

        self::assertInstanceOf(RouteParameterDto::class, $dto);
        self::assertSame(5, $dto->getInt());
        self::assertSame('foo', $dto->getString());
    }

    public function testDefaultInvalidQueryParameterForConstructorArgument(): void
    {
        $request = new Request(['requiredArgument' => 'invalid']);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(DummyClassA::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());

        $this->expectException(ConstraintViolationException::class);

        $resolver->resolve($request, $argument)->current();
    }

    public function testDefaultInvalidQueryParameterHandling(): void
    {
        $request = new Request(['int' => 'invalid', 'string' => 'foo']);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(QueryDtoWithAttribute::class, []);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessageIs('ConstraintViolation: This value should be of type int.');

        $resolver->resolve($request, $argument)->current();
    }

    /**
     * @return \Iterator<array<mixed>>
     */
    public static function errorTestData(): \Iterator
    {
        yield [
            [],
            DummyClassA::class,
            ArgumentCountConstraintViolation::class,
        ];

        yield [
            ['requiredArgument' => 'test'],
            DummyClassA::class,
            NotNormalizableValueConstraintViolation::class,
        ];

        yield [
            ['nonExistingArgument' => 1],
            DummyClassA::class,
            MissingConstructorArgumentsConstraintViolation::class,
        ];

        yield [
            ['requiredArgument' => 1, 'items' => null],
            ArrayDto::class,
            NotNormalizableValueConstraintViolation::class,
        ];

        yield [
            ['items' => null],
            IntArrayDto::class,
            NotNormalizableValueConstraintViolation::class,
        ];

        yield [
            ['objectArgument' => ['requiredArgument' => null]],
            NestedDto::class,
            NotNormalizableValueConstraintViolation::class,
        ];

        yield [
            ['objectArgument' => null],
            NestedDto::class,
            MissingConstructorArgumentsConstraintViolation::class,
        ];
    }
}
