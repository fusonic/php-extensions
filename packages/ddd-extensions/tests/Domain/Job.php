<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\Domain;

use Fusonic\DDDExtensions\Domain\Model\EntityInterface;
use Fusonic\DDDExtensions\Domain\Model\Traits\IntegerIdTrait;

final class Job implements EntityInterface
{
    use IntegerIdTrait;

    public function __construct(private readonly ?string $name)
    {
    }

    public function getId(): JobId
    {
        return new JobId($this->id);
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
