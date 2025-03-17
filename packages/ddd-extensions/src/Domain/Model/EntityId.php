<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

abstract readonly class EntityId extends ValueObject implements \Stringable
{
    abstract public function isDefined(): bool;

    abstract public function getValue(): mixed;
}
