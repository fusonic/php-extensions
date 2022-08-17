<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

abstract class AbstractId extends ValueObject
{
    abstract public function __toString(): string;

    abstract public function isNull(): bool;

    abstract public function getValue(): mixed;
}
