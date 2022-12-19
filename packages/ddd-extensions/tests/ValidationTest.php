<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Domain\Exception\DomainAssertionFailedException;
use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;
use Fusonic\DDDExtensions\Tests\Domain\User;

class ValidationTest extends AbstractTestCase
{
    public function testValidationException(): void
    {
        $exception = null;

        try {
            new User('');
        } catch (DomainAssertionFailedException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        echo $exception->getMessage();
        self::assertSame(
            'The following 1 assertions failed:'.\PHP_EOL.'1) User.name: Value "" is empty, but non empty value was expected.'.\PHP_EOL,
            $exception->getMessage()
        );
    }

    public function testValidationLazyException(): void
    {
        $exception = null;

        try {
            new AddressValueObject('', '');
        } catch (DomainAssertionFailedException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        echo $exception->getMessage();
        self::assertSame(
            'The following 2 assertions failed:'.\PHP_EOL.
            '1) AddressValueObject.street: Value "" is empty, but non empty value was expected.'.\PHP_EOL.
            '2) AddressValueObject.number: Value "" is empty, but non empty value was expected.'.\PHP_EOL,
            $exception->getMessage()
        );
    }
}
