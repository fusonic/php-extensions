<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fusonic_http_kernel');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('strict_route_types')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('strict_query_types')
                    ->defaultValue(false)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
