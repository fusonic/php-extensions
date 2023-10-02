<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\DependencyInjection;

use Fusonic\HttpKernelBundle\Provider\ContextAwareProviderInterface;
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
        $loader->load('services.php');

        $container->registerForAutoconfiguration(ContextAwareProviderInterface::class)
            ->addTag(ContextAwareProviderInterface::TAG_CONTEXT_AWARE_PROVIDER);
    }
}
