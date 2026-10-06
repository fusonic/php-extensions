<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Request;

use Fusonic\HttpKernelBundle\Request\RequestDataCollector;
use Fusonic\HttpKernelBundle\Tests\Dto\DummyClassB;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

final class RequestDataCollectorTest extends TestCase
{
    public function testRouteParamsAreForcedToIntegersAndQueryParamsAreKept(): void
    {
        $request = new Request(['someProperty' => '123'], [], ['_route_params' => ['requiredArgument' => '1']]);

        $data = new RequestDataCollector()->collect($request, DummyClassB::class);

        self::assertSame(1, $data['requiredArgument']);
        self::assertSame('123', $data['someProperty']);
    }

    public function testRouteParamsAreNotForcedToIntegers(): void
    {
        $request = new Request([], [], ['_route_params' => ['requiredArgument' => '1']]);

        $data = new RequestDataCollector(forceRouteParamsIntegers: false)->collect($request, DummyClassB::class);

        self::assertSame('1', $data['requiredArgument']);
    }

    public function testTypeEnforcementIsDisabledForRequestsWithoutBody(): void
    {
        $collector = new RequestDataCollector();

        self::assertSame([AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true], $collector->getDenormalizationContext(Request::create('/', Request::METHOD_GET)));
        self::assertSame([], $collector->getDenormalizationContext(Request::create('/', Request::METHOD_POST)));
    }
}
