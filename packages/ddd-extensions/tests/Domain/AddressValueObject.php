<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\Domain;

use Fusonic\DDDExtensions\Domain\Model\ValueObject;
use Fusonic\DDDExtensions\Domain\Validation\Assert;

final class AddressValueObject extends ValueObject
{
    private string $street;
    private string $number;

    public function __construct(string $street, string $number)
    {
        Assert::lazy($this)
            ->that($street, 'street')->notEmpty()
            ->that($number, 'number')->notEmpty()
            ->verifyNow();

        $this->street = $street;
        $this->number = $number;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function equals(ValueObject $object): bool
    {
        return $object instanceof self
            && $object->getStreet() === $this->getStreet()
            && $object->getNumber() === $this->getNumber();
    }

    public function __toString(): string
    {
        return sprintf('%s, %s', $this->street, $this->number);
    }
}
