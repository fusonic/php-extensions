<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;

class ValueObjectTest extends AbstractTestCase
{
    public function testEquals(): void
    {
        $address1 = new AddressValueObject('Street', '1');
        $address2 = new AddressValueObject('Street', '1');

        self::assertTrue($address1->equals($address2));
    }

    public function testToString(): void
    {
        $address1 = new AddressValueObject('Street', '1');

        self::assertSame('Street, 1', (string) $address1);
    }
}
