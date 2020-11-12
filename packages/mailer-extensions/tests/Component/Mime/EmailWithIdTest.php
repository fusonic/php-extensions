<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

namespace Fusonic\MailerExtensions\Tests\Component\Mime;

use Fusonic\MailerExtensions\Component\Mime\EmailWithId;
use PHPStan\Testing\TestCase;

class EmailWithIdTest extends TestCase
{
    public function test(): void
    {
        $email = new EmailWithId('123');

        $this->assertSame('123', $email->getId());

        $serialized = serialize($email);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(EmailWithId::class, $unserialized);
        $this->assertSame('123', $unserialized->getId());
    }
}
