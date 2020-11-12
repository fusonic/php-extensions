<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\MailerExtensions\Contracts;

use Fusonic\MailerExtensions\Component\Mime\EmailWithId;

interface EmailWithIdMessageHandledHandler
{
    public function handle(EmailWithId $message): void;
}
