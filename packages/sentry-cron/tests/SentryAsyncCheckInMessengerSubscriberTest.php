<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron\Tests;

use Fusonic\SentryCron\AsyncCheckInScheduleEventInterface;
use Fusonic\SentryCron\AsyncCheckInScheduleEventTrait;
use Fusonic\SentryCron\SentryAsyncCheckInMessengerSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

final class SentryAsyncCheckInMessengerSubscriberTest extends TestCase
{
    public function testConstructor(): void
    {
        $subscriber = new SentryAsyncCheckInMessengerSubscriber(true, new FakeCheckInCapturer());

        self::assertSame([
            WorkerMessageHandledEvent::class => 'onMessageHandled',
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ], $subscriber::getSubscribedEvents());
    }

    public function testCompletesLastAsyncMessageOnceHandled(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentryAsyncCheckInMessengerSubscriber(true, $capturer);

        $message = $this->createAsyncMessage();
        $message->setCheckInId('checkin_1');
        $message->markAsLast();

        $subscriber->onMessageHandled($this->handledEvent($message));

        self::assertSame('completed', $capturer->getCheckInIds()['checkin_1']);
    }

    public function testDoesNotCompleteNonLastAsyncMessage(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentryAsyncCheckInMessengerSubscriber(true, $capturer);

        $message = $this->createAsyncMessage();
        $message->setCheckInId('checkin_1');

        $subscriber->onMessageHandled($this->handledEvent($message));

        self::assertArrayNotHasKey('checkin_1', $capturer->getCheckInIds());
    }

    public function testReportsErrorOnFinalFailure(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentryAsyncCheckInMessengerSubscriber(true, $capturer);

        $message = $this->createAsyncMessage();
        $message->setCheckInId('checkin_1');

        $subscriber->onMessageFailed($this->failedEvent($message, willRetry: false));

        self::assertSame('error', $capturer->getCheckInIds()['checkin_1']);
        self::assertTrue($message->hasFailed());
    }

    public function testDoesNotReportErrorWhenMessengerWillRetry(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentryAsyncCheckInMessengerSubscriber(true, $capturer);

        $message = $this->createAsyncMessage();
        $message->setCheckInId('checkin_1');

        $subscriber->onMessageFailed($this->failedEvent($message, willRetry: true));

        self::assertArrayNotHasKey('checkin_1', $capturer->getCheckInIds());
        self::assertFalse($message->hasFailed());
    }

    public function testLastMessageAfterEarlierFailureReReportsErrorInsteadOfComplete(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentryAsyncCheckInMessengerSubscriber(true, $capturer);

        $message = $this->createAsyncMessage();
        $message->setCheckInId('checkin_1');
        $message->markAsFailed();
        $message->markAsLast();

        $subscriber->onMessageHandled($this->handledEvent($message));

        self::assertSame('error', $capturer->getCheckInIds()['checkin_1']);
    }

    public function testDisabledSubscriberDoesNothing(): void
    {
        $capturer = new FakeCheckInCapturer();
        $subscriber = new SentryAsyncCheckInMessengerSubscriber(false, $capturer);

        $message = $this->createAsyncMessage();
        $message->setCheckInId('checkin_1');
        $message->markAsLast();

        $subscriber->onMessageHandled($this->handledEvent($message));

        self::assertSame([], $capturer->getCheckInIds());
    }

    private function createAsyncMessage(): AsyncCheckInScheduleEventInterface
    {
        return new class implements AsyncCheckInScheduleEventInterface {
            use AsyncCheckInScheduleEventTrait;
        };
    }

    private function handledEvent(object $message): WorkerMessageHandledEvent
    {
        return new WorkerMessageHandledEvent(new Envelope($message), 'async_background');
    }

    private function failedEvent(object $message, bool $willRetry): WorkerMessageFailedEvent
    {
        $event = new WorkerMessageFailedEvent(new Envelope($message), 'async_background', new \RuntimeException('failed'));

        if ($willRetry) {
            $event->setForRetry();
        }

        return $event;
    }
}
