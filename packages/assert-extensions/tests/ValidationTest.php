<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\AssertExtensions\Tests;

use Fusonic\AssertExtensions\Exception\AssertionFailedException;
use Fusonic\AssertExtensions\Validation\Assert;

final class ValidationTest extends AbstractTestCase
{
    public function testValidationException(): void
    {
        $exception = null;

        try {
            $value = '';
            Assert::that('User', 'name', $value)
                ->notEmpty()
                ->alnum();
        } catch (AssertionFailedException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertSame(
            'The following 1 assertions failed:'.\PHP_EOL.'1) User.name: Value "" is empty, but non empty value was expected.'.\PHP_EOL,
            $exception->getMessage()
        );
    }

    public function testValidationLazyException(): void
    {
        $exception = null;

        try {
            $value = '';
            Assert::lazy('User')
                ->that($value, 'number')->notEmpty()
                ->verifyNow();
        } catch (AssertionFailedException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertSame(
            'The following 1 assertions failed:'.\PHP_EOL.
            '1) User.number: Value "" is empty, but non empty value was expected.',
            $exception->getMessage()
        );
    }
}
