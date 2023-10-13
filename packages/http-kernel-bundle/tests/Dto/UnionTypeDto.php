<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Dto;

class UnionTypeDto
{
    public function __construct(
        /**
         * @var array<StringIdDto|SubTypeDto>|null
         */
        public readonly ?array $unionTypes,

        public readonly int|StringIdDto|null $unionType = null
    ) {
    }
}
