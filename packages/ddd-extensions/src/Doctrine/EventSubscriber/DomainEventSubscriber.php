<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Fusonic\DDDExtensions\Domain\Event\DomainEventInterface;
use Fusonic\DDDExtensions\Event\DomainEventHandlerTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Collects all the events raised inside the Aggregate objects in the domain. When Doctrine `flush` is called
 * the events are dispatched.
 */
class DomainEventSubscriber implements EventSubscriber
{
    use DomainEventHandlerTrait;

    public function __construct(
        private readonly MessageBusInterface $bus
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
        $this->addObject($args->getObject());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->addObject($args->getObject());
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->addObject($args->getObject());
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $this->dispatchEvents();
    }

    protected function dispatchEvent(DomainEventInterface $event): void
    {
        $this->bus->dispatch($event);
    }
}
