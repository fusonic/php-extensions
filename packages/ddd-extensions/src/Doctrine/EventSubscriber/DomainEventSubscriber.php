<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Fusonic\DDDExtensions\Domain\Model\AggregateRoot;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Collects all the events raised inside the Aggregate objects in the domain. When Doctrine `flush` is called
 * the events are dispatched.
 */
class DomainEventSubscriber implements EventSubscriber
{
    /**
     * @var AggregateRoot[]
     */
    private array $entities = [];

    public function __construct(
        private MessageBusInterface $bus,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
            Events::postFlush,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->keepAggregateRoots($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->keepAggregateRoots($args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->keepAggregateRoots($args);
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->entities as $entity) {
            foreach ($entity->popEvents() as $event) {
                $this->bus->dispatch($event);

                $this->logger?->debug(sprintf('DomainEvent dispatched: %s', $event::class));
            }
        }
    }

    private function keepAggregateRoots(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!($entity instanceof AggregateRoot)) {
            return;
        }

        $this->entities[] = $entity;
    }
}
