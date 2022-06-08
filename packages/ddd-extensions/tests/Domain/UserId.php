<?php

namespace Fusonic\DDDExtensions\Tests\Domain;

use Fusonic\DDDExtensions\Domain\Model\AbstractId;

class UserId extends AbstractId
{
    public function __construct(private int $id)
    {
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
