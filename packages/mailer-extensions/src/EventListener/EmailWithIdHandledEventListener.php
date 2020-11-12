<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\MailerExtensions\EventListener;

use Fusonic\MailerExtensions\Component\Mime\EmailWithId;
use Fusonic\MailerExtensions\Contracts\EmailWithIdMessageHandledHandler;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

class EmailWithIdHandledEventListener
{
    private EmailWithIdMessageHandledHandler $handler;

    public function __construct(EmailWithIdMessageHandledHandler $handler)
    {
        $this->handler = $handler;
    }

    public function onWorkerMessageHandledEvent(WorkerMessageHandledEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if (!$message instanceof SendEmailMessage) {
            return;
        }

        $email = $message->getMessage();

        if (!$email instanceof EmailWithId) {
            return;
        }

        $this->handler->handle($email);
    }
}
