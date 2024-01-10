<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Controller;

use Fusonic\HttpKernelBundle\Attribute\FromRequest;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Tests\Dto\EnumArrayDto;
use Fusonic\HttpKernelBundle\Tests\Dto\EnumDto;
use Fusonic\HttpKernelBundle\Tests\Dto\ExampleIntBackedEnum;
use Fusonic\HttpKernelBundle\Tests\Dto\ExampleStringBackedEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParameterEnumTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testQueryParameterValidEnumHandling(): void
    {
        $query = [
            'exampleEnum' => ExampleStringBackedEnum::CHOICE_1->value,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(EnumDto::class, $dto);
        self::assertSame(ExampleStringBackedEnum::CHOICE_1, $dto->exampleEnum);
    }

    public function testQueryParameterValidEnumArrayHandling(): void
    {
        $query = [
            'stringEnums' => [ExampleStringBackedEnum::CHOICE_1->value, ExampleStringBackedEnum::CHOICE_2->value],
            'intEnums' => [(string) ExampleIntBackedEnum::INT_1->value, (string) ExampleIntBackedEnum::INT_2->value],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(EnumArrayDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(EnumArrayDto::class, $dto);
        self::assertSame([ExampleStringBackedEnum::CHOICE_1, ExampleStringBackedEnum::CHOICE_2], $dto->stringEnums);
    }

    public function testQueryParameterInvalidStringEnumArrayHandling(): void
    {
        $query = [
            'stringEnums' => ['WRONG_CHOICE', ExampleStringBackedEnum::CHOICE_2->value],
            'intEnums' => [],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(EnumArrayDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('stringEnums[0]', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame(
            'The value you selected is not a valid choice.',
            $constraintViolationList->get(0)->getMessage()
        );
        self::assertSame('WRONG_CHOICE', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterInvalidIntEnumArrayHandling(): void
    {
        $query = [
            'stringEnums' => [],
            'intEnums' => ['9'],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(EnumArrayDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('intEnums[0]', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame(
            'The value you selected is not a valid choice.',
            $constraintViolationList->get(0)->getMessage()
        );
        self::assertSame('9', $constraintViolationList->get(0)->getInvalidValue());
        self::assertSame([1, 2], $constraintViolationList->get(0)->getParameters()['{{ choices }}']);
    }

    public function testQueryParameterInvalidEnumHandling(): void
    {
        $query = [
            'exampleEnum' => 'WRONG_CHOICE',
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('exampleEnum', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame(
            'The value you selected is not a valid choice.',
            $constraintViolationList->get(0)->getMessage()
        );
        self::assertSame('WRONG_CHOICE', $constraintViolationList->get(0)->getInvalidValue());
    }
}
