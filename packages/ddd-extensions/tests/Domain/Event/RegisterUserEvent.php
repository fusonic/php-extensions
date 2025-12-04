<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\Domain\Event;

use Fusonic\DDDExtensions\Domain\Event\DomainEventInterface;
use Fusonic\DDDExtensions\Tests\Domain\UserId;

final readonly class RegisterUserEvent implements DomainEventInterface
{
    public function __construct(public UserId $userId)
    {
    }
}
