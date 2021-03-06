<?php

namespace Fusonic\MessengerMailerBundle\Tests\EmailAttachmentHandler;

use Fusonic\MessengerMailerBundle\Component\Mime\AttachmentEmail;
use Fusonic\MessengerMailerBundle\EmailAttachmentHandler\NullAttachmentHandler;
use PHPUnit\Framework\TestCase;

class NullAttachmentHandlerTest extends TestCase
{
    public function testWriteAndRemove(): void
    {
        $handler = new NullAttachmentHandler();
        $email = new AttachmentEmail();

        $content = 'inline file content';
        $name = 'inline-file.txt';
        $email->attachPersisted($content, $name);

        $filename = $handler->writeAttachment($email, $name, $content);

        self::assertSame($name, $filename);

        $handler->removeAttachments($email);
    }
}
