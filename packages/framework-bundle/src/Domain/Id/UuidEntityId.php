<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Domain\Id;

use Fusonic\DDDExtensions\Domain\Model\EntityId;
use Fusonic\DDDExtensions\Domain\Model\ValueObject;
use Symfony\Component\Uid\Uuid;

abstract readonly class UuidEntityId extends EntityId implements \Stringable
{
    private Uuid $id;

    final public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v7();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function isDefined(): bool
    {
        return true;
    }

    public function getValue(): Uuid
    {
        return $this->id;
    }

    public function equals(ValueObject $object): bool
    {
        return $object instanceof self
            && $this->getValue()->equals($object->getValue());
    }

    public static function fromString(string $uuid): static
    {
        return new static(Uuid::fromString($uuid));
    }
}
