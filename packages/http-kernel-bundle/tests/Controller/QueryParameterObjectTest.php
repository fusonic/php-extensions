<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Controller;

use Fusonic\HttpKernelBundle\Attribute\FromRequest;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Tests\Dto\NestedDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParameterObjectTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testQueryParameterObjectHandling(): void
    {
        $query = [
            'objectArgument' => [
                'requiredArgument' => '10',
            ],
            'nestedItems' => [
                ['objectArgument' => ['requiredArgument' => '20']],
            ],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(NestedDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $dto = $resolver->resolve($request, $argument)->current();

        self::assertInstanceOf(NestedDto::class, $dto);
        self::assertSame(10, $dto->getObjectArgument()->getRequiredArgument());
        self::assertNotNull($dto->nestedItems);
        self::assertSame(20, $dto->nestedItems[0]->getObjectArgument()->getRequiredArgument());
    }

    public function testQueryParameterInvalidObjectHandling(): void
    {
        $query = [
            'objectArgument' => [
                'requiredArgument' => '1',
            ],
            'nestedItems' => [
                ['objectArgument' => ['requiredArgument' => 'invalid']],
            ],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(NestedDto::class, [new FromRequest()]);

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
        self::assertSame('nestedItems[0].objectArgument.requiredArgument', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('invalid', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterScalarForObject(): void
    {
        $request = new Request(['objectArgument' => '1']);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(NestedDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $this->expectException(ConstraintViolationException::class);

        $generator->current();
    }
}
