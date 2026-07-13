<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class FusonicApiDocumentationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'fusonic_api_documentation.request_object_class',
            $config['request_object_class'] ?? null
        );

        $exceptionContext = $config['exception_context_class'] ?? [];
        $container->setParameter(
            'fusonic_api_documentation.exception_context_class',
            $exceptionContext['class'] ?? null
        );
        $container->setParameter(
            'fusonic_api_documentation.exception_context_method',
            $exceptionContext['method'] ?? null
        );
    }
}
