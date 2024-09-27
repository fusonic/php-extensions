<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\Assert\Tests;

use Fusonic\Assert\Assert;
use Fusonic\Assert\AssertionFailedException;
use PHPUnit\Framework\TestCase;

final class AssertTest extends TestCase
{
    public function testLazyAssertion(): void
    {
        $username = '';
        $password = '1123';

        self::expectException(AssertionFailedException::class);
        self::expectExceptionMessage(
            'The following 2 assertions failed:'.\PHP_EOL.'1) User.username: Value "" is empty, but non empty value was expected.'.\PHP_EOL.'2) User.password: Value "1123" is too short, it should have at least 8 characters, but only has 4 characters.'.\PHP_EOL
        );

        Assert::lazy('User')
            ->that($username, 'username')
            ->notEmpty()
            ->that($password, 'password')
            ->minLength(8)
            ->maxLength(30)
            ->verifyNow();
    }

    public function testAssertion(): void
    {
        $username = 'notempty';

        self::expectException(AssertionFailedException::class);
        self::expectExceptionMessage(
            'The following 1 assertions failed:'.\PHP_EOL.'1) User.username: Value "notempty" has to be 10 exactly characters long, but length is 8.'.\PHP_EOL
        );

        Assert::that('User', 'username', $username)->notEmpty()->length(10);
    }

    public function testAssertionWithClassRoot(): void
    {
        $username = '';

        self::expectException(AssertionFailedException::class);
        self::expectExceptionMessage(
            'The following 1 assertions failed:'.\PHP_EOL.'1) Dummy.username: Value "" is empty, but non empty value was expected.'.\PHP_EOL
        );

        $model = new Dummy();

        Assert::that($model, 'username', $username)->notEmpty();
    }
}
