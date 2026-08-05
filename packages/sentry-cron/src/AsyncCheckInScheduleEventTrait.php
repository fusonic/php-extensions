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
    private bool $hasFailed = false;

    public function isLast(): bool
    {
        return $this->isLast;
    }

    public function markAsLast(): static
    {
        $this->isLast = true;

        return $this;
    }

    public function getCheckInId(): ?string
    {
        return $this->checkInId;
    }

    public function setCheckInId(?string $checkInId): static
    {
        $this->checkInId = $checkInId;

        return $this;
    }

    public function hasFailed(): bool
    {
        return $this->hasFailed;
    }

    public function markAsFailed(): static
    {
        $this->hasFailed = true;

        return $this;
    }
}
