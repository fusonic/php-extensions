<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\DependencyInjection;

use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
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

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'fusonic_http_kernel.strict_query_params',
            $config['strict_query_params'] ?? null
        );

        $container->setParameter(
            'fusonic_http_kernel.strict_route_params',
            $config['strict_route_params'] ?? null
        );

        $loader->load('services.php');

        $definition = $container->getDefinition(RequestDtoResolver::class);

        if (isset($config['strict_query_params'])) {
            $definition->replaceArgument('$strictQueryParams', $config['strict_query_params']);
        }

        if (isset($config['strict_route_params'])) {
            $definition->replaceArgument('$strictRouteParams', $config['strict_route_params']);
        }

        $container->registerForAutoconfiguration(ContextAwareProviderInterface::class)
            ->addTag(ContextAwareProviderInterface::TAG_CONTEXT_AWARE_PROVIDER);
    }
}
