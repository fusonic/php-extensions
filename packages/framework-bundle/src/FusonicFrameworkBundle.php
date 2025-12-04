<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle;

use Fusonic\DDDExtensions\Doctrine\LifecycleListener\DomainEventLifecycleListener;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\CommandBus;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\EventBus;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\QueryBus;
use Fusonic\FrameworkBundle\Application\Messenger\Handler\CommandHandlerInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Handler\EventHandlerInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Handler\QueryHandlerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class FusonicFrameworkBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        /** @var ArrayNodeDefinition<TreeBuilder<'array'>> $rootNode */
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->arrayNode('messenger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('bus')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('command_bus')
                                    ->cannotBeEmpty()
                                    ->defaultValue('command.bus')
                                    ->info('Service ID for the command bus')
                                    ->example('command.bus')
                                ->end()
                                ->scalarNode('event_bus')
                                    ->cannotBeEmpty()
                                    ->defaultValue('event.bus')
                                    ->info('Service ID for the event bus')
                                    ->example('event.bus')
                                ->end()
                                ->scalarNode('query_bus')
                                    ->cannotBeEmpty()
                                    ->defaultValue('query.bus')
                                    ->info('Service ID for the query bus')
                                    ->example('query.bus')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('fusonic_framework.messenger.bus.command_bus', $config['messenger']['bus']['command_bus']);
        $container->parameters()->set('fusonic_framework.messenger.bus.event_bus', $config['messenger']['bus']['event_bus']);
        $container->parameters()->set('fusonic_framework.messenger.bus.query_bus', $config['messenger']['bus']['query_bus']);

        $container->import('../config/services.php');

        /*
         * Configuration related to Symfony Messenger ↓
         */
        $builder->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag('messenger.message_handler', ['bus' => $config['messenger']['bus']['command_bus']]);

        $builder->registerForAutoconfiguration(EventHandlerInterface::class)
            ->addTag('messenger.message_handler', ['bus' => $config['messenger']['bus']['event_bus']]);

        $builder->registerForAutoconfiguration(QueryHandlerInterface::class)
            ->addTag('messenger.message_handler', ['bus' => $config['messenger']['bus']['query_bus']]);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /*
         * Configuration related to Symfony Messenger bus helpers ↓
         */
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                /** @var array<class-string, string> $busMap */
                $busMap = [
                    CommandBus::class => 'fusonic_framework.messenger.bus.command_bus',
                    QueryBus::class => 'fusonic_framework.messenger.bus.query_bus',
                    EventBus::class => 'fusonic_framework.messenger.bus.event_bus',
                ];

                foreach ($busMap as $class => $param) {
                    /** @var string $busServiceId */
                    $busServiceId = $container->getParameter($param);

                    $definition = $container->findDefinition($class);
                    $definition->setArgument('$bus', new Reference($busServiceId));
                }
            }
        });

        /*
         * Configuration related to fusonic/ddd-extensions ↓
         */
        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                /** @var string $eventBusServiceId */
                $eventBusServiceId = $container->getParameter('fusonic_framework.messenger.bus.event_bus');

                $definition = $container->findDefinition(DomainEventLifecycleListener::class);
                $definition->setArgument('$bus', new Reference($eventBusServiceId));
            }
        });
    }
}
