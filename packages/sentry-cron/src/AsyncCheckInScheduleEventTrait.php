<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron;

trait AsyncCheckInScheduleEventTrait
{
    private bool $isLast = false;
    private ?string $checkInId = null;

    public function isLast(): bool
    {
        return $this->isLast;
    }

    public function markAsLast(): void
    {
        $this->isLast = true;
    }

    public function getCheckInId(): ?string
    {
        return $this->checkInId;
    }

    public function setCheckInId(?string $checkInId): void
    {
        $this->checkInId = $checkInId;
    }
}
