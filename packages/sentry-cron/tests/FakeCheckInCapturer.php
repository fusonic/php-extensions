<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron\Tests;

use Fusonic\SentryCron\CheckInCapturer\CheckInCapturerInterface;
use Fusonic\SentryCron\SentryMonitorConfig;

/**
 * A CheckInCapturer that is used for testing.
 */
class FakeCheckInCapturer implements CheckInCapturerInterface
{
    /**
     * @var array<string, string>
     */
    private array $checkIns = [];

    private ?string $lastCheckInId = null;

    private ?SentryMonitorConfig $lastConfig = null;

    private static int $checkInIndex = 0;

    public function start(string $messageClass, string $cronExpression, SentryMonitorConfig $attribute): ?string
    {
        ++self::$checkInIndex;

        $checkInId = \sprintf('checkin_%d', self::$checkInIndex);

        $this->checkIns[$checkInId] = 'started';
        $this->lastCheckInId = $checkInId;
        $this->lastConfig = $attribute;

        return $checkInId;
    }

    public function complete(string $messageClass, string $checkInId): void
    {
        $this->checkIns[$checkInId] = 'completed';
    }

    public function error(string $messageClass, string $checkInId): void
    {
        $this->checkIns[$checkInId] = 'error';
    }

    public function getLastCheckInId(): ?string
    {
        return $this->lastCheckInId;
    }

    public function getLastConfig(): ?SentryMonitorConfig
    {
        return $this->lastConfig;
    }

    /**
     * @return array<string, string>
     */
    public function getCheckInIds(): array
    {
        return $this->checkIns;
    }
}
