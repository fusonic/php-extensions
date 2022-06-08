<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Tests\Domain\Event\RegisterUserEvent;
use Fusonic\DDDExtensions\Tests\Domain\User;
use Fusonic\DDDExtensions\Tests\Domain\UserId;

class AggregateRootTest extends AbstractTestCase
{
    public function testId(): void
    {
        $user = new User('John');

        self::assertSame((string) new UserId(0), (string) $user->getId());
        self::assertTrue((new UserId(0))->equals($user->getId()));
    }

    public function testEvent(): void
    {
        $user = new User('John');

        $user->register();

        $events = $user->popEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(RegisterUserEvent::class, $events[0]);
    }
}
