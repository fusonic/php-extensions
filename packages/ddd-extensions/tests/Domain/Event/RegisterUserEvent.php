<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\Domain\Event;

use Fusonic\DDDExtensions\Domain\Event\DomainEventInterface;
use Fusonic\DDDExtensions\Tests\Domain\User;
use Fusonic\DDDExtensions\Tests\Domain\UserId;

final class RegisterUserEvent implements DomainEventInterface
{
    private UserId $userId;

    public function __construct(User $user)
    {
        $this->userId = $user->getId();
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }
}
