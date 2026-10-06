<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Controller;

use Fusonic\HttpKernelBundle\Attribute\FromRequest;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Tests\Dto\UnionTypeDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParameterUnionTypeTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testQueryParameterUnionType(): void
    {
        $query = [
            'unionType' => 'false',
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(UnionTypeDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $this->expectExceptionMessageIs('Using union types with non-scalar types in the url is not supported. Type: Fusonic\\HttpKernelBundle\\Tests\\Dto\\StringIdDto|int');

        $generator->current();
    }

    public function testQueryParameterScalarUnionType(): void
    {
        $query = [
            'unionTypes' => null,
            'intOrString' => '1',
            'floatOrBool' => 'true',
            'intOrStringItems' => ['2', 'foo'],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(UnionTypeDto::class, [new FromRequest()]);

        $dto = $this->getRequestDtoResolver()->resolve($request, $argument)->current();

        self::assertInstanceOf(UnionTypeDto::class, $dto);
        self::assertSame(1, $dto->intOrString);
        self::assertTrue($dto->floatOrBool);
        self::assertSame([2, 'foo'], $dto->intOrStringItems);
    }

    public function testQueryParameterInvalidScalarUnionType(): void
    {
        $request = new Request(['unionTypes' => null, 'floatOrBool' => 'foo']);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(UnionTypeDto::class, [new FromRequest()]);

        $generator = $this->getRequestDtoResolver()->resolve($request, $argument);

        $this->expectException(ConstraintViolationException::class);
        $this->expectExceptionMessageIs('ConstraintViolation: This value should be of type bool|float.');

        $generator->current();
    }
}
