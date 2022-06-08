<?php

namespace Fusonic\DDDExtensions\Tests\Domain;

use Fusonic\DDDExtensions\Domain\Model\AbstractId;

class JobId extends AbstractId
{
    public function __construct(private int $id)
    {
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
