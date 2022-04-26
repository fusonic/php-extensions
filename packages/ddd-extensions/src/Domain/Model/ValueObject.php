<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

abstract class ValueObject
{
    /**
     * Compare the ValueObject with another ValueObject.
     */
    abstract public function equals(self $object): bool;

    /**
     * All value objects should have a __toString method. This
     * makes debugging and logging easier.
     */
    abstract public function __toString(): string;
}
