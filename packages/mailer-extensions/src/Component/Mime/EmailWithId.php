<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\MailerExtensions\Component\Mime;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractPart;

class EmailWithId extends Email
{
    private string $id;

    public function __construct(string $id, Headers $headers = null, AbstractPart $body = null)
    {
        $this->id = $id;

        parent::__construct($headers, $body);
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
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
