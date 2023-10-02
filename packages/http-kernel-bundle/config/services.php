<?php

declare(strict_types=1);

use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
use Fusonic\HttpKernelBundle\Normalizer\ConstraintViolationExceptionNormalizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RequestDtoResolver::class)
        ->autowire()
        ->tag('controller.argument_value_resolver', [
            'priority' => 50,
        ]);

    $services->set(ConstraintViolationExceptionNormalizer::class)
        ->arg('$normalizer', service('serializer.normalizer.constraint_violation_list'));
};
