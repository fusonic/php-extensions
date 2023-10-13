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

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $this->expectExceptionMessage('Using union types in the url is not supported. Type: (Fusonic\HttpKernelBundle\Tests\Dto\StringIdDto|int|null)');

        $generator->current();
    }
}
