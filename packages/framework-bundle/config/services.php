<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Fusonic\FrameworkBundle\Application\Messenger\Handler\CommandHandlerInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Handler\EventHandlerInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Handler\QueryHandlerInterface;
use Fusonic\FrameworkBundle\Infrastructure\Resolver\UuidEntityIdValueResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->instanceof(CommandHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $services->instanceof(QueryHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'query.bus']);

    $services->instanceof(EventHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'event.bus']);

    $services->set(UuidEntityIdValueResolver::class)
        // Priority 150 is important so that the custom resolver is called before Symfony's RequestValueResolver
        ->tag('controller.argument_value_resolver', ['priority' => 150]);
};
