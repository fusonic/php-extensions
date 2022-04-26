<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model\Traits;

trait IdTrait
{
    /**
     * The `getId` will return 0 if it hasn't been flushed to the database yet. After it is
     * flushed, the ORM will populate the `id` with a real value.
     *
     * To check if an entity is not saved yet, check for `id === 0`.
     */
    protected ?int $id = null;

    public function getId(): int
    {
        return $this->id ?? 0;
    }
}
