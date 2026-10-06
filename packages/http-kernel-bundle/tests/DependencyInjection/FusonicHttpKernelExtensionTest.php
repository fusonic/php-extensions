<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\DependencyInjection;

use Fusonic\HttpKernelBundle\DependencyInjection\FusonicHttpKernelExtension;
use Fusonic\HttpKernelBundle\Request\RequestDataCollector;
use Fusonic\HttpKernelBundle\Request\RequestDataCollectorInterface;
use Fusonic\HttpKernelBundle\Request\StrictRequestDataCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FusonicHttpKernelExtensionTest extends TestCase
{
    public function testDefaultRequestDataCollector(): void
    {
        $container = $this->loadExtension([]);

        self::assertSame(RequestDataCollector::class, (string) $container->getAlias(RequestDataCollectorInterface::class));
    }

    public function testStrictRequestDataCollector(): void
    {
        $container = $this->loadExtension([['strict' => true]]);

        self::assertSame(StrictRequestDataCollector::class, (string) $container->getAlias(RequestDataCollectorInterface::class));
    }

    /**
     * @param array<array<string, mixed>> $configs
     */
    private function loadExtension(array $configs): ContainerBuilder
    {
        $container = new ContainerBuilder();
        new FusonicHttpKernelExtension()->load($configs, $container);

        return $container;
    }
}
