<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron\CheckInCapturer;

use Fusonic\SentryCron\SentryMonitorConfig;
use Sentry\CheckInStatus;
use Sentry\MonitorConfig;
use Sentry\MonitorSchedule;

use function Sentry\captureCheckIn;
use function Symfony\Component\String\u;

class SentryCheckInCapturer implements CheckInCapturerInterface
{
    /**
     * @param class-string $messageClass
     */
    public function start(string $messageClass, string $cronExpression, SentryMonitorConfig $attribute): ?string
    {
        $monitorConfig = new MonitorConfig(
            MonitorSchedule::crontab($cronExpression),
            checkinMargin: $attribute->checkinMargin,
            maxRuntime: $attribute->maxRuntime,
            failureIssueThreshold: $attribute->failureIssueThreshold,
            recoveryThreshold: $attribute->maxRuntime,
        );

        return captureCheckIn(
            slug: $this->getMessageSlug($messageClass),
            status: CheckInStatus::inProgress(),
            monitorConfig: $monitorConfig,
        );
    }

    public function complete(string $messageClass, string $checkInId): void
    {
        captureCheckIn(
            slug: $this->getMessageSlug($messageClass),
            status: CheckInStatus::ok(),
            checkInId: $checkInId,
        );
    }

    public function error(string $messageClass, string $checkInId): void
    {
        captureCheckIn(
            slug: $this->getMessageSlug($messageClass),
            status: CheckInStatus::error(),
            checkInId: $checkInId
        );
    }

    /**
     * @param class-string $messageClass
     */
    protected function getMessageSlug(string $messageClass): string
    {
        return (string) u(self::getClassBasename($messageClass))->snake();
    }

    private static function getClassBasename(string $className): string
    {
        /** @var string $basename */
        $basename = strrchr($className, '\\');

        return substr($basename, 1);
    }
}
