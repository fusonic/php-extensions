<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Tests\Domain\JobId;

final class AbstractIdTest extends AbstractTestCase
{
    public function testEquals(): void
    {
        $id1 = new JobId(1);
        $id2 = new JobId(1);
        $id3 = new JobId(2);

        self::assertTrue($id1->equals($id2));
        self::assertTrue($id2->equals($id1));
        self::assertEquals($id1, $id2);

        self::assertFalse($id1->equals($id3));
        self::assertFalse($id3->equals($id1));
        self::assertNotEquals($id1, $id3);
    }

    public function testIsNull(): void
    {
        $id1 = new JobId(null);

        self::assertTrue($id1->isNull());
        self::assertSame('0', (string) $id1);
    }

    public function testClone(): void
    {
        $id1 = new JobId(1);
        $id2 = clone $id1;

        self::assertTrue($id1->equals($id2));
        self::assertTrue($id2->equals($id1));
        self::assertEquals($id1, $id2);
    }
}
