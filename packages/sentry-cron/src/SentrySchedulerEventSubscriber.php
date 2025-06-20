<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron;

use Sentry\CheckInStatus;
use Sentry\MonitorConfig;
use Sentry\MonitorSchedule;
use Symfony\Component\Scheduler\Event\FailureEvent;
use Symfony\Component\Scheduler\Event\PostRunEvent;
use Symfony\Component\Scheduler\Event\PreRunEvent;
use Symfony\Component\Scheduler\Trigger\AbstractDecoratedTrigger;
use Symfony\Component\Scheduler\Trigger\CronExpressionTrigger;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

use function Sentry\captureCheckIn;
use function Symfony\Component\String\u;

/**
 * Event subscriber that triggers Sentry monitoring status checks for scheduled jobs.
 */
class SentrySchedulerEventSubscriber
{
    /**
     * @var array<string, string>
     */
    private array $checkInIds = [];

    public function __construct(
        private readonly bool $enabled,
        private readonly int $checkinMarginInMinutes = 5,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostRunEvent::class => 'onPostRun',
            PreRunEvent::class => 'onPreRun',
            FailureEvent::class => 'onFailure',
        ];
    }

    /**
     * @return class-string<TriggerInterface>[]
     */
    private static function supportedTriggers(): array
    {
        return [CronExpressionTrigger::class];
    }

    public function onPreRun(PreRunEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $messageId = $event->getMessageContext()->id;
        $trigger = $event->getMessageContext()->trigger;

        if ($trigger instanceof AbstractDecoratedTrigger) {
            $trigger = $trigger->inner();
        }

        if (!\in_array($trigger::class, self::supportedTriggers(), true)) {
            return;
        }

        $monitorConfig = new MonitorConfig(
            schedule: MonitorSchedule::crontab((string) $trigger),
            checkinMargin: $this->checkinMarginInMinutes
        );

        $checkInId = captureCheckIn(
            slug: $this->getMessageSlug($event->getMessage()),
            status: CheckInStatus::inProgress(),
            monitorConfig: $monitorConfig,
        );

        if (null !== $checkInId) {
            $this->checkInIds[$messageId] = $checkInId;
        }
    }

    public function onPostRun(PostRunEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $messageId = $event->getMessageContext()->id;
        $checkInId = $this->checkInIds[$messageId] ?? null;

        if (null !== $checkInId) {
            captureCheckIn(
                slug: $this->getMessageSlug($event->getMessage()),
                status: CheckInStatus::ok(),
                checkInId: $checkInId,
            );

            unset($this->checkInIds[$messageId]);
        }
    }

    public function onFailure(FailureEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $messageId = $event->getMessageContext()->id;
        $checkInId = $this->checkInIds[$messageId] ?? null;

        if (null !== $checkInId) {
            captureCheckIn(
                slug: $this->getMessageSlug($event->getMessage()),
                status: CheckInStatus::error()
            );

            unset($this->checkInIds[$messageId]);
        }
    }

    protected function getMessageSlug(object $message): string
    {
        /** @var string $basename */
        $basename = strrchr($message::class, '\\');

        return (string) u(substr($basename, 1))->kebab();
    }
}
