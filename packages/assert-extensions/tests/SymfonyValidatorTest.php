<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\AssertExtensions\Tests;

use Fusonic\AssertExtensions\Exception\AssertionFailedException;
use Fusonic\AssertExtensions\Validation\Assert;

final class SymfonyValidatorTest extends AbstractTestCase
{
    public function testValidationException(): void
    {
        $exception = null;

        try {
            $value = '';
            Assert::that('User', 'name', $value)
                ->notEmpty();
        } catch (AssertionFailedException $e) {
            $exception = $e;
        }

        $constraintViolations = $exception->getConstraintViolationList();
        self::assertCount(1, $constraintViolations);

        self::assertSame('Value "" is empty, but non empty value was expected.', $constraintViolations->get(0)->getMessage());
        self::assertSame('name', $constraintViolations->get(0)->getPropertyPath());
        self::assertSame('User', $constraintViolations->get(0)->getRoot());
    }

    public function testValidationLazyException(): void
    {
        $exception = null;

        try {
            Assert::lazy('User')
                ->that('', 'number')->notEmpty()
                ->that('&', 'name')->alnum()
                ->verifyNow();
        } catch (AssertionFailedException $e) {
            $exception = $e;
        }

        $constraintViolations = $exception->getConstraintViolationList();
        self::assertCount(2, $constraintViolations);

        self::assertSame('Value "" is empty, but non empty value was expected.', $constraintViolations->get(0)->getMessage());
        self::assertSame('number', $constraintViolations->get(0)->getPropertyPath());
        self::assertSame('User', $constraintViolations->get(0)->getRoot());

        self::assertSame('Value "&" is not alphanumeric, starting with letters and containing only letters and numbers.', $constraintViolations->get(1)->getMessage());
        self::assertSame('name', $constraintViolations->get(1)->getPropertyPath());
        self::assertSame('User', $constraintViolations->get(1)->getRoot());
    }
}
