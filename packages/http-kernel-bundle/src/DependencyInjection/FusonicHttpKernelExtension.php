<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\DependencyInjection;

use Fusonic\HttpKernelBundle\Provider\ContextAwareProviderInterface;
use Fusonic\HttpKernelBundle\Request\RequestDataCollector;
use Fusonic\HttpKernelBundle\Request\RequestDataCollectorInterface;
use Fusonic\HttpKernelBundle\Request\StrictRequestDataCollector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class FusonicHttpKernelExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            container: $container,
            locator: new FileLocator(__DIR__.'/../../config')
        );

        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader->load('services.php');

        $container->setAlias(
            RequestDataCollectorInterface::class,
            $config['strict'] ? StrictRequestDataCollector::class : RequestDataCollector::class,
        );

        $container->registerForAutoconfiguration(ContextAwareProviderInterface::class)
            ->addTag(ContextAwareProviderInterface::TAG_CONTEXT_AWARE_PROVIDER);
    }
}
