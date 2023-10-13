<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Tests\Domain\JobId;

final class EntityIntegerIdTest extends AbstractTestCase
{
    public function testEquals(): void
    {
        $id1 = new JobId(1);
        $id2 = new JobId(1);
        $id3 = new JobId(2);

        self::assertTrue($id1->equals($id2));
        self::assertTrue($id2->equals($id1));
        self::assertObjectEquals($id1, $id2);
        self::assertSame($id1->getValue(), $id2->getValue());

        self::assertFalse($id1->equals($id3));
        self::assertFalse($id3->equals($id1));
        self::assertNotSame($id1, $id3);
    }

    public function testIsDefined(): void
    {
        $id1 = new JobId(null);

        self::assertFalse($id1->isDefined());
        self::assertSame('0', (string) $id1);
    }

    public function testClone(): void
    {
        $id1 = new JobId(1);
        $id2 = clone $id1;

        self::assertTrue($id1->equals($id2));
        self::assertTrue($id2->equals($id1));
        self::assertObjectEquals($id1, $id2);
    }
}
