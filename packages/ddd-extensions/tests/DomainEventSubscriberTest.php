<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Fusonic\DDDExtensions\Doctrine\EventSubscriber\DomainEventSubscriber;
use Fusonic\DDDExtensions\Tests\Domain\Event\RegisterUserEvent;
use Fusonic\DDDExtensions\Tests\Domain\Job;
use Fusonic\DDDExtensions\Tests\Domain\User;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;

final class DomainEventSubscriberTest extends AbstractTestCase
{
    #[IgnoreDeprecations]
    public function testRaisingEventsOnAggregateRoot(): void
    {
        if (!class_exists(\Doctrine\ORM\Event\LifecycleEventArgs::class)) {
            self::markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs has been removed as of doctrine/orm^3.0');
        }

        $messageBus = $this->getMessageBus();
        $subscriber = new DomainEventSubscriber($messageBus);  // @phpstan-ignore new.deprecated
        $user = new User('John');
        $user->register();

        $lifeCycleEvent = new \Doctrine\ORM\Event\LifecycleEventArgs($user, $this->getEntityManager());

        $subscriber->postPersist($lifeCycleEvent);
        $subscriber->postUpdate($lifeCycleEvent);
        $subscriber->postRemove($lifeCycleEvent);

        $postFlushEvent = $this->createMock(PostFlushEventArgs::class);
        $subscriber->postFlush($postFlushEvent);

        $messages = $messageBus->getDispatchedMessages();

        self::assertCount(1, $messages);
        self::assertInstanceOf(RegisterUserEvent::class, $messages[0]['message']);
    }

    #[IgnoreDeprecations]
    public function testRaisingEventsOnNonAggregateRoot(): void
    {
        if (!class_exists(\Doctrine\ORM\Event\LifecycleEventArgs::class)) {
            self::markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs has been removed as of doctrine/orm^3.0');
        }

        $messageBus = $this->getMessageBus();
        $subscriber = new DomainEventSubscriber($messageBus); // @phpstan-ignore new.deprecated
        $job = new Job('Project Manager');

        $lifeCycleEvent = new \Doctrine\ORM\Event\LifecycleEventArgs($job, $this->getEntityManager());

        $subscriber->postPersist($lifeCycleEvent);

        $postFlushEvent = $this->createMock(PostFlushEventArgs::class);
        $subscriber->postFlush($postFlushEvent);

        $messages = $messageBus->getDispatchedMessages();

        self::assertCount(0, $messages);
    }

    #[IgnoreDeprecations]
    public function testSubscribedEvents(): void
    {
        if (!class_exists(\Doctrine\ORM\Event\LifecycleEventArgs::class)) {
            self::markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs has been removed as of doctrine/orm^3.0');
        }

        $messageBus = $this->getMessageBus();
        $subscriber = new DomainEventSubscriber($messageBus);  // @phpstan-ignore new.deprecated

        self::assertSame([
            'postPersist',
            'postUpdate',
            'postRemove',
            'postFlush',
        ], $subscriber->getSubscribedEvents());
    }
}
