<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class IntArrayDto
{
    /**
     * @param int[]             $items
     * @param array<mixed>|null $nullableItems
     */
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Valid]
        #[Assert\All(new Assert\Positive())]
        private readonly array $items,
        public readonly ?array $nullableItems = null,
    ) {
    }

    /**
     * @return int[]
     */
    public function getItems(): ?array
    {
        return $this->items;
    }
}
