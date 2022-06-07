<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\DependencyInjection\Compiler;

use Fusonic\ApiDocumentationBundle\Describer\DocumentedRouteDescriber;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class ConfigurationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('nelmio_api_doc')) {
            throw new LogicException(sprintf('%s is not configured.', NelmioApiDocBundle::class));
        }

        /** @var string[] $areas */
        $areas = $container->getParameter('nelmio_api_doc.areas');

        foreach ($areas as $area) {
            $container->register(sprintf('fusonic_api_documentation.describers.openapi_php.%s', $area), DocumentedRouteDescriber::class)
                ->setPublic(false)
                ->setArguments([
                    new Reference(sprintf('nelmio_api_doc.routes.%s', $area)),
                    new Reference('nelmio_api_doc.controller_reflector'),
                    new Reference('logger'),
                    $container->getParameter('fusonic_api_documentation.request_object_class'),
                ])
                ->addTag(sprintf('nelmio_api_doc.describer.%s', $area), ['priority' => -199]);
        }
    }
}
