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
     * @internal Used by {@see SentrySchedulerEventSubscriber} and {@see SentryAsyncCheckInMessengerSubscriber}
     */
    public function getCheckInId(): ?string;

    /**
     * @internal Used by {@see SentrySchedulerEventSubscriber} and {@see SentryAsyncCheckInMessengerSubscriber}
     */
    public function setCheckInId(?string $checkId): void;

    /**
     * Check if a step of this async check-in has already reported a failure.
     */
    public function hasFailed(): bool;

    /**
     * Mark this async check-in as having failed, so a later successful step
     * cannot overwrite the failure with a completed status.
     */
    public function markAsFailed(): void;
}
