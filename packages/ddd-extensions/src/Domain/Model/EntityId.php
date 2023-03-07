<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

abstract class EntityId extends ValueObject
{
    abstract public function __toString(): string;

    abstract public function isDefined(): bool;

    abstract public function getValue(): mixed;
}
