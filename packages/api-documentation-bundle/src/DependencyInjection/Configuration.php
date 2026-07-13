<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fusonic_api_documentation');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('request_object_class')
                ->end()
                ->arrayNode('exception_context_class')
                    ->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('method')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
