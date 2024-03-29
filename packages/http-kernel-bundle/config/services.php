<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
use Fusonic\HttpKernelBundle\Normalizer\ConstraintViolationExceptionNormalizer;
use Fusonic\HttpKernelBundle\Normalizer\DecoratedBackedEnumNormalizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RequestDtoResolver::class)
        ->autowire()
        ->tag('controller.argument_value_resolver', [
            'priority' => 50,
        ]);

    $services->set(DecoratedBackedEnumNormalizer::class)
        ->decorate('serializer.normalizer.backed_enum')
        ->args([service('.inner')]);

    $services->set(ConstraintViolationExceptionNormalizer::class)
        ->autoconfigure()
        ->arg('$normalizer', service('serializer.normalizer.constraint_violation_list'));
};
