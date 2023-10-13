<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Exception;

class UnionTypeNotSupportedException extends \RuntimeException
{
    public function __construct(string $type)
    {
        parent::__construct(sprintf('Using union types in the url is not supported. Type: %s', $type));
    }

    public static function fromReflectionUnionType(\ReflectionUnionType $reflectionUnionType): self
    {
        return new self(
            sprintf(
                '(%s)',
                implode(
                    '|',
                    array_map(static function (\ReflectionNamedType $type) {
                        return $type->getName();
                    },
                        $reflectionUnionType->getTypes())
                )
            )
        );
    }
}
