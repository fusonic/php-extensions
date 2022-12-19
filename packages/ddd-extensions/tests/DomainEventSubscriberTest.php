<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Fusonic\DDDExtensions\Doctrine\EventSubscriber\DomainEventSubscriber;
use Fusonic\DDDExtensions\Tests\Domain\Event\RegisterUserEvent;
use Fusonic\DDDExtensions\Tests\Domain\Job;
use Fusonic\DDDExtensions\Tests\Domain\User;
use Psr\Log\NullLogger;

class DomainEventSubscriberTest extends AbstractTestCase
{
    public function testRaisingEventsOnAggregateRoot(): void
    {
        $messageBus = $this->getMessageBus();
        $logger = new NullLogger();
        $subscriber = new DomainEventSubscriber($messageBus, $logger);
        $user = new User('John');
        $user->register();

        $lifeCycleEvent = new LifecycleEventArgs($user, $this->getEntityManager());

        $subscriber->postPersist($lifeCycleEvent);
        $subscriber->postUpdate($lifeCycleEvent);
        $subscriber->postRemove($lifeCycleEvent);

        $postFlushEvent = $this->createMock(PostFlushEventArgs::class);
        $subscriber->postFlush($postFlushEvent);

        $messages = $messageBus->getDispatchedMessages();

        self::assertCount(1, $messages);
        self::assertInstanceOf(RegisterUserEvent::class, $messages[0]['message']);
    }

    public function testRaisingEventsOnNonAggregateRoot(): void
    {
        $messageBus = $this->getMessageBus();
        $logger = new NullLogger();
        $subscriber = new DomainEventSubscriber($messageBus, $logger);
        $job = new Job('Project Manager');

        $lifeCycleEvent = new LifecycleEventArgs($job, $this->getEntityManager());

        $subscriber->postPersist($lifeCycleEvent);

        $postFlushEvent = $this->createMock(PostFlushEventArgs::class);
        $subscriber->postFlush($postFlushEvent);

        $messages = $messageBus->getDispatchedMessages();

        self::assertCount(0, $messages);
    }

    public function testSubscribedEvents(): void
    {
        $messageBus = $this->getMessageBus();
        $logger = new NullLogger();
        $subscriber = new DomainEventSubscriber($messageBus, $logger);

        self::assertSame([
            'postPersist',
            'postUpdate',
            'postRemove',
            'postFlush',
        ], $subscriber->getSubscribedEvents());
    }
}
