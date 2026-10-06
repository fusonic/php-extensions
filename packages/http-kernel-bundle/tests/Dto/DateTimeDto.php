<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Dto;

final readonly class DateTimeDto
{
    /**
     * @param \DateTimeImmutable[] $dates
     */
    public function __construct(
        public \DateTimeInterface $date,
        public array $dates = [],
    ) {
    }
}
