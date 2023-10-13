<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Fusonic\DDDExtensions\Doctrine\LifecycleListener\DomainEventLifecycleListener;
use Fusonic\DDDExtensions\Tests\Domain\Event\RegisterUserEvent;
use Fusonic\DDDExtensions\Tests\Domain\Job;
use Fusonic\DDDExtensions\Tests\Domain\User;

final class DomainEventLifecycleListenerTest extends AbstractTestCase
{
    public function testRaisingEventsOnAggregateRoot(): void
    {
        $messageBus = $this->getMessageBus();
        $listener = new DomainEventLifecycleListener($messageBus);
        $user = new User('John');
        $user->register();

        $em = $this->getEntityManager();
        $listener->postPersist(new PostPersistEventArgs($user, $em));
        $listener->postUpdate(new PostUpdateEventArgs($user, $em));
        $listener->postRemove(new PostRemoveEventArgs($user, $em));

        $postFlushEvent = $this->createMock(PostFlushEventArgs::class);
        $listener->postFlush($postFlushEvent);

        $messages = $messageBus->getDispatchedMessages();

        self::assertCount(1, $messages);
        self::assertInstanceOf(RegisterUserEvent::class, $messages[0]['message']);
    }

    public function testRaisingEventsOnNonAggregateRoot(): void
    {
        $messageBus = $this->getMessageBus();
        $listener = new DomainEventLifecycleListener($messageBus);
        $job = new Job('Project Manager');

        $lifeCycleEvent = new PostPersistEventArgs($job, $this->getEntityManager());

        $listener->postPersist($lifeCycleEvent);

        $postFlushEvent = $this->createMock(PostFlushEventArgs::class);
        $listener->postFlush($postFlushEvent);

        $messages = $messageBus->getDispatchedMessages();

        self::assertCount(0, $messages);
    }
}
