<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App\Response;

use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * @template T
 */
class TestGenericResponse
{
    /**
     * @param array<T> $data
     */
    public function __construct(
        #[Ignore]
        private readonly array $data,
    ) {
    }

    /**
     * @return array<T>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
