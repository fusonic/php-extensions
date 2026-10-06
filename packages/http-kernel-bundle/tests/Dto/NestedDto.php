<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class NestedDto
{
    /**
     * @param array<NestedDto>|null $nestedItems
     */
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Valid]
        private readonly DummyClassA $objectArgument,
        public readonly ?array $nestedItems,
    ) {
    }

    public function getObjectArgument(): DummyClassA
    {
        return $this->objectArgument;
    }
}
