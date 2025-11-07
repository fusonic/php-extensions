<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron\Tests;

use Cron\CronExpression;
use Fusonic\SentryCron\AsyncCheckInScheduleEventInterface;
use Fusonic\SentryCron\AsyncCheckInScheduleEventTrait;
use Fusonic\SentryCron\SentryMonitorConfig;
use Fusonic\SentryCron\SentrySchedulerEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Scheduler\Event\FailureEvent;
use Symfony\Component\Scheduler\Event\PostRunEvent;
use Symfony\Component\Scheduler\Event\PreRunEvent;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Component\Scheduler\Trigger\JitterTrigger;
use Symfony\Component\Scheduler\Trigger\PeriodicalTrigger;

final class SentrySchedulerEventSubscriberTest extends TestCase
{
    private const TEST_ID = '123';

    public function testConstructor(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(true, $capturer);

        self::assertSame([
            PostRunEvent::class => 'onPostRun',
            PreRunEvent::class => 'onPreRun',
            FailureEvent::class => 'onFailure',
        ], $subscriber::getSubscribedEvents());
    }

    public function testMessageWithConfig(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(true, $capturer);
        $message = new #[SentryMonitorConfig(checkinMargin: 10)] class() {
        };

        $context = $this->createContext();
        $subscriber->onPreRun($this->mockPreRunEvent($message, $context));

        self::assertSame('started', $capturer->getCheckInIds()[$capturer->getLastCheckInId()]);
    }

    public function testDecoratedTriggerMessage(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(true, $capturer);
        $message = new class {
        };

        $context = $this->createDecoratedContext();
        $subscriber->onPreRun($this->mockPreRunEvent($message, $context));

        self::assertSame('started', $capturer->getCheckInIds()[$capturer->getLastCheckInId()]);
    }

    public function testUnsupportedTriggerMessage(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(true, $capturer);
        $message = new class {
        };

        $context = $this->createUnsupportedTriggerContext();
        $subscriber->onPreRun($this->mockPreRunEvent($message, $context));

        self::assertNull($capturer->getLastCheckInId());
    }

    public function testAsyncMessage(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(true, $capturer);

        $message = new class implements AsyncCheckInScheduleEventInterface {
            use AsyncCheckInScheduleEventTrait;
        };

        $context = $this->createContext();

        $subscriber->onPreRun($this->mockPreRunEvent($message, $context));

        $lastCheckInId = $capturer->getLastCheckInId();

        self::assertSame($lastCheckInId, $message->getCheckInId());
        self::assertFalse($message->isLast());
        self::assertSame('started', $capturer->getCheckInIds()[$lastCheckInId]);

        $subscriber->onPostRun($this->mockPostRunEvent($message, $context));

        self::assertSame('started', $capturer->getCheckInIds()[$lastCheckInId]);
        self::assertFalse($message->isLast());

        $message->markAsLast();

        self::assertTrue($message->isLast());

        $subscriber->onPostRun($this->mockPostRunEvent($message, $context));

        self::assertSame('completed', $capturer->getCheckInIds()[$lastCheckInId]);
    }

    public function testDisabledSubscriber(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(false, $capturer);
        $message = new class {
        };

        $context = $this->createContext();
        $subscriber->onPreRun($this->mockPreRunEvent($message, $context));

        self::assertNull($capturer->getLastCheckInId());
    }

    private function createDecoratedContext(): MessageContext
    {
        $trigger = new JitterTrigger(new CronExpressionTrigger(new CronExpression('* * * * *')));

        return new MessageContext('TestScheduleEvent', self::TEST_ID, $trigger, new \DateTimeImmutable());
    }

    private function mockPreRunEvent(object $message, MessageContext $context): PreRunEvent
    {
        $event = $this->createMock(PreRunEvent::class);
        $event->method('getMessageContext')->willReturn($context);
        $event->method('getMessage')->willReturn($message);

        return $event;
    }

    private function mockPostRunEvent(object $message, MessageContext $context): PostRunEvent
    {
        $event = $this->createMock(PostRunEvent::class);
        $event->method('getMessageContext')->willReturn($context);
        $event->method('getMessage')->willReturn($message);

        return $event;
    }

    private function mockFailureEvent(object $message, MessageContext $context): FailureEvent
    {
        $event = $this->createMock(FailureEvent::class);
        $event->method('getMessageContext')->willReturn($context);
        $event->method('getMessage')->willReturn($message);

        return $event;
    }

    public function testCompletedMessage(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(true, $capturer);
        $message = new class {
        };

        $context = $this->createContext();
        $subscriber->onPreRun($this->mockPreRunEvent($message, $context));

        $lastCheckInId = $capturer->getLastCheckInId();

        self::assertNotNull($lastCheckInId);
        self::assertSame('started', $capturer->getCheckInIds()[$lastCheckInId]);

        $subscriber->onPostRun($this->mockPostRunEvent($message, $context));

        self::assertSame('completed', $capturer->getCheckInIds()[$lastCheckInId]);
    }

    public function testFailedMessage(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentrySchedulerEventSubscriber(true, $capturer);
        $message = new class {
        };

        $context = $this->createContext();
        $subscriber->onPreRun($this->mockPreRunEvent($message, $context));

        $lastCheckInId = $capturer->getLastCheckInId();

        self::assertNotNull($lastCheckInId);
        self::assertSame('started', $capturer->getCheckInIds()[$lastCheckInId]);

        $subscriber->onFailure($this->mockFailureEvent($message, $context));

        self::assertSame('error', $capturer->getCheckInIds()[$lastCheckInId]);
    }

    private function createContext(): MessageContext
    {
        $trigger = new CronExpressionTrigger(new CronExpression('* * * * *'));

        return new MessageContext('TestScheduleEvent', self::TEST_ID, $trigger, new \DateTimeImmutable());
    }

    private function createUnsupportedTriggerContext(): MessageContext
    {
        $trigger = new JitterTrigger(new PeriodicalTrigger('1 day'));

        return new MessageContext('TestScheduleEvent', self::TEST_ID, $trigger, new \DateTimeImmutable());
    }
}
