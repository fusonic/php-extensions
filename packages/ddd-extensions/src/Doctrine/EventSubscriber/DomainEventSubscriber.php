<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Fusonic\DDDExtensions\Doctrine\LifecycleListener\DomainEventLifecycleListener;
use Fusonic\DDDExtensions\Domain\Event\DomainEventInterface;
use Fusonic\DDDExtensions\Event\DomainEventHandlerTrait;
use Symfony\Component\Messenger\MessageBusInterface;

if (class_exists(\Doctrine\ORM\Event\LifecycleEventArgs::class)) {
    /**
     * Collects all the events raised inside the Aggregate objects in the domain. When Doctrine `flush` is called
     * the events are dispatched.
     *
     * @deprecated since fusonic/ddd-extensions 1.1, use {@see DomainEventLifecycleListener} instead
     */
    class DomainEventSubscriber implements EventSubscriber
    {
        use DomainEventHandlerTrait;

        public function __construct(
            private readonly MessageBusInterface $bus
        ) {
            trigger_deprecation(
                'fusonic/ddd-extensions',
                '1.1',
                'Using "%s" is deprecated, use "%s" instead.',
                self::class,
                DomainEventLifecycleListener::class
            );
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

        // @phpstan-ignore class.notFound
        public function postPersist(\Doctrine\ORM\Event\LifecycleEventArgs $args): void
        {
            $this->addObject($args->getObject()); // @phpstan-ignore class.notFound
        }

        // @phpstan-ignore class.notFound
        public function postUpdate(\Doctrine\ORM\Event\LifecycleEventArgs $args): void
        {
            $this->addObject($args->getObject()); // @phpstan-ignore class.notFound
        }

        // @phpstan-ignore class.notFound
        public function postRemove(\Doctrine\ORM\Event\LifecycleEventArgs $args): void
        {
            $this->addObject($args->getObject()); // @phpstan-ignore class.notFound
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
}
