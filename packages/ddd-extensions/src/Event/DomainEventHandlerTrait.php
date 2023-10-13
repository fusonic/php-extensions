<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Event;

use Fusonic\DDDExtensions\Domain\Event\DomainEventInterface;
use Fusonic\DDDExtensions\Domain\Model\AggregateRoot;

/**
 * A trait that contains the common logic you will need in an event listener to actually dispatch/handle
 * the domain events.
 */
trait DomainEventHandlerTrait
{
    /**
     * @var AggregateRoot[]
     */
    private array $entities = [];

    /**
     * Add an object to the entities array if it is an {@link AggregateRoot}.
     */
    private function addObject(object $entity): void
    {
        if (!($entity instanceof AggregateRoot)) {
            return;
        }

        $this->entities[] = $entity;
    }

    private function dispatchEvents(): void
    {
        foreach ($this->entities as $entity) {
            foreach ($entity->popEvents() as $event) {
                $this->dispatchEvent($event);
            }
        }
    }

    abstract protected function dispatchEvent(DomainEventInterface $event): void;
}
