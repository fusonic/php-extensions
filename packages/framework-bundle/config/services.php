<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Fusonic\DDDExtensions\Doctrine\LifecycleListener\DomainEventLifecycleListener;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\CommandBus;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\CommandBusInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\EventBus;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\EventBusInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\QueryBus;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\QueryBusInterface;
use Fusonic\FrameworkBundle\Infrastructure\Normalizer\UuidEntityIdNormalizer;
use Fusonic\FrameworkBundle\Infrastructure\Resolver\UuidEntityIdValueResolver;
use Fusonic\FrameworkBundle\Port\Http\UuidEntityIdModelDescriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    /*
     * Configuration related to UuidEntityId ↓
     */
    $services->set(UuidEntityIdModelDescriber::class)
        // Priority 150 is important so that the custom model describer is called before Nelmio's ObjectModelDescriber
        ->tag('nelmio_api_doc.model_describer', ['priority' => 150]);

    $services->set(UuidEntityIdNormalizer::class)
        // Priority -850 is important so that the custom normalizer is called before Symfonys's UidNormalizer
        ->tag('serializer.normalizer', ['priority' => -850]);

    $services->set(UuidEntityIdValueResolver::class)
        // Priority 150 is important so that the custom resolver is called before Symfony's RequestValueResolver
        ->tag('controller.argument_value_resolver', ['priority' => 150]);

    /*
     * Configuration related to Symfony Messenger bus helpers ↓
     */
    $services->set(CommandBus::class);
    $services->alias(CommandBusInterface::class, CommandBus::class);

    $services->set(EventBus::class);
    $services->alias(EventBusInterface::class, EventBus::class);

    $services->set(QueryBus::class);
    $services->alias(QueryBusInterface::class, QueryBus::class);

    /*
     * Configuration related to fusonic/ddd-extensions ↓
     */
    $services->set(DomainEventLifecycleListener::class)
        ->tag('doctrine.event_listener', ['event' => 'postPersist', 'priority' => 500])
        ->tag('doctrine.event_listener', ['event' => 'postUpdate', 'priority' => 500])
        ->tag('doctrine.event_listener', ['event' => 'postRemove', 'priority' => 500])
        ->tag('doctrine.event_listener', ['event' => 'postFlush', 'priority' => 500]);
};
