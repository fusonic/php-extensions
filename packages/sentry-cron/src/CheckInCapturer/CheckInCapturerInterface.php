<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron\CheckInCapturer;

use Fusonic\SentryCron\SentryMonitorConfig;

interface CheckInCapturerInterface
{
    /**
     * @param class-string $messageClass
     */
    public function start(string $messageClass, string $cronExpression, SentryMonitorConfig $attribute): ?string;

    /**
     * @param class-string $messageClass
     */
    public function complete(string $messageClass, string $checkInId): void;

    /**
     * @param class-string $messageClass
     */
    public function error(string $messageClass, string $checkInId): void;
}
