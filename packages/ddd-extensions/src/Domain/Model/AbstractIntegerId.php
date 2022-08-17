<?php

namespace Fusonic\DDDExtensions\Domain\Model;

/**
 * A base class for id value objects that use an integer internally.
 * If you are using Doctrine with integers as ids you can use this as a base class.
 */
abstract class AbstractIntegerId extends AbstractId
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

    public function isNull(): bool
    {
        return self::DEFAULT_VALUE === $this->id;
    }

    public function equals(ValueObject $object): bool
    {
        return $object instanceof self && (string) $this === (string) $object;
    }
}
