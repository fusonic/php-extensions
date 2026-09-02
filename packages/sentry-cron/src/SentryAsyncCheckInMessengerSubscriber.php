<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron;

use Fusonic\SentryCron\CheckInCapturer\CheckInCapturerInterface;
use Fusonic\SentryCron\CheckInCapturer\SentryCheckInCapturer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

/**
 * Completes or fails Sentry check-ins for {@see AsyncCheckInScheduleEventInterface} messages that
 * were re-dispatched onto a Messenger transport, and therefore never pass back through Symfony
 * Scheduler's own PostRunEvent/FailureEvent (those only fire for the message Scheduler itself
 * invoked, not for follow-up messages a handler dispatches onto another transport).
 */
class SentryAsyncCheckInMessengerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly bool $enabled,
        private readonly CheckInCapturerInterface $checkInCapturer = new SentryCheckInCapturer(),
    ) {
    }

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageHandledEvent::class => 'onMessageHandled',
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $message = $event->getEnvelope()->getMessage();

        if (!$message instanceof AsyncCheckInScheduleEventInterface || !$message->isLast()) {
            return;
        }

        $checkInId = $message->getCheckInId();

        if (null === $checkInId) {
            return;
        }

        if ($message->hasFailed()) {
            // A previous step already reported a failure for this check-in; re-report it
            // here (rather than complete()) in case that earlier report never reached Sentry.
            $this->checkInCapturer->error($message::class, $checkInId);

            return;
        }

        $this->checkInCapturer->complete($message::class, $checkInId);
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if (!$this->enabled || $event->willRetry()) {
            return;
        }

        $message = $event->getEnvelope()->getMessage();

        if (!$message instanceof AsyncCheckInScheduleEventInterface) {
            return;
        }

        $message->markAsFailed();

        $checkInId = $message->getCheckInId();

        if (null !== $checkInId) {
            $this->checkInCapturer->error($message::class, $checkInId);
        }
    }
}
