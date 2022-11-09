<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\MessengerMailerBundle\Component\Mime;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

class TemplatedAttachmentEmail extends TemplatedEmail implements AttachmentEmailInterface
{
    use AttachmentEmailTrait;

    public function __construct(Headers $headers = null, AbstractPart $body = null)
    {
        $this->id = $this->generateRandomId();

        parent::__construct($headers, $body);
    }

    public function __serialize(): array
    {
        return [$this->id, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->id, $parentData] = $data;

        parent::__unserialize($parentData);
    }
}
