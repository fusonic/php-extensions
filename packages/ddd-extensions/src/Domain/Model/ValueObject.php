<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

abstract readonly class ValueObject
{
    /**
     * Compare the ValueObject with another ValueObject.
     */
    abstract public function equals(self $object): bool;
}
