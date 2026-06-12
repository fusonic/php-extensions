<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class DocumentedError
{
    /**
     * @var string[]
     */
    public readonly array $methods;

    /**
     * @param class-string<\Throwable> $exceptionClass
     * @param array<string>|string     $methods
     */
    public function __construct(
        public readonly string $exceptionClass,
        public readonly int $statusCode,
        public readonly ?string $description = null,
        array|string $methods = [],
    ) {
        $this->methods = array_map(strtolower(...), (array) $methods);
    }
}
