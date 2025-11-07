<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron;

interface AsyncCheckInScheduleEventInterface
{
    /**
     * Check if an async check-in event is the last one.
     */
    public function isLast(): bool;

    /**
     * Mark an async event check-in as the last one.
     */
    public function markAsLast(): void;

    /**
     * @internal Used by {@see SentrySchedulerEventSubscriber}
     */
    public function getCheckInId(): ?string;

    /**
     * @internal Used by {@see SentrySchedulerEventSubscriber}
     */
    public function setCheckInId(?string $checkId): void;
}
