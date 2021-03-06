<?php

namespace Fusonic\MessengerMailerBundle\Tests;

use Fusonic\MessengerMailerBundle\Component\Mime\AttachmentEmail;
use Fusonic\MessengerMailerBundle\EmailAttachmentHandler\FilesystemAttachmentHandler;
use Fusonic\MessengerMailerBundle\EventSubscriber\AttachmentEmailEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Mime\Email;

class WorkerMessageHandledTest extends TestCase
{
    use TestSetupTrait;

    public function testNonSendEmailMessage(): void
    {
        $attachmentHandler = new FilesystemAttachmentHandler($this->getAttachmentDirectory());
        $eventSubscriber = new AttachmentEmailEventSubscriber($attachmentHandler);

        $dummyClass = new class() {
        };
        $event = new WorkerMessageHandledEvent(new Envelope($dummyClass), 'bus');
        $eventSubscriber->onWorkerMessageHandledEvent($event);

        self::assertNotInstanceOf(SendEmailMessage::class, $event->getEnvelope()->getMessage());
    }

    public function testNonAttachableEmail(): void
    {
        $attachmentHandler = new FilesystemAttachmentHandler($this->getAttachmentDirectory());
        $eventSubscriber = new AttachmentEmailEventSubscriber($attachmentHandler);

        $email = new Email();

        $event = new WorkerMessageHandledEvent(new Envelope(new SendEmailMessage($email)), 'bus');
        $eventSubscriber->onWorkerMessageHandledEvent($event);

        self::assertInstanceOf(SendEmailMessage::class, $event->getEnvelope()->getMessage());

        $message = $event->getEnvelope()->getMessage();
        $emailMessage = $message->getMessage();

        self::assertNotInstanceOf(AttachmentEmail::class, $emailMessage);
    }
}
