<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Controller;

use Fusonic\HttpKernelBundle\Attribute\FromRequest;
use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Tests\Dto\IntArrayDto;
use Fusonic\HttpKernelBundle\Tests\Dto\NestedDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParameterArrayTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testQueryParameterArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', '3'],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(IntArrayDto::class, $dto);
        self::assertSame([1, 2, 3], $dto->getItems());
    }

    public function testQueryParameterInvalidArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', 'true'],
            'nullableItems' => null,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('items[2]', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('true', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterInvalidNestedArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', 'nested' => 'true'],
            'nullableItems' => null,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('items.nested', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('true', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterInvalidDeepNestedArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', 'nested' => ['deep' => '1']],
            'nullableItems' => null,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('items.nested', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('[]', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterWithObjectArrayNotSupported(): void
    {
        $query = [
            'objectArgument' => [
                'requiredArgument' => '1',
            ],
            'nestedItems' => [
                ['objectArgument' => '1'],
            ],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(NestedDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $this->expectExceptionMessage('Using object types in the url is not supported.');

        $generator->current();
    }
}
