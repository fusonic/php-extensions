<?php

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

/**
 * A base class for id value objects that use an integer internally.
 * If you are using Doctrine with integers as ids you can use this as a base class.
 */
abstract class EntityIntegerId extends EntityId
{
    public const DEFAULT_VALUE = 0;
    private int $id;

    public function __construct(?int $id)
    {
        $this->id = $id ?? self::DEFAULT_VALUE;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function isDefined(): bool
    {
        return self::DEFAULT_VALUE !== $this->id;
    }

    public function getValue(): int
    {
        return $this->id;
    }

    public function equals(ValueObject $object): bool
    {
        return $object instanceof self && $this->getValue() === $object->getValue();
    }
}
