<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Doctrine\LifecycleListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Fusonic\DDDExtensions\Domain\Event\DomainEventInterface;
use Fusonic\DDDExtensions\Event\DomainEventHandlerTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Collects all the events raised inside the Aggregate objects in the domain. When Doctrine `flush` is called
 * the events are dispatched.
 */
class DomainEventLifecycleListener
{
    use DomainEventHandlerTrait;

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->addObject($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->addObject($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
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
