<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Tests\Unit\Infrastructure\Validator;

final class TestDto
{
    public function __construct(
        public TestId $id = new TestId(),
        public ?TestId $optionalId = null,
        public string $name = '',
    ) {
    }
}
