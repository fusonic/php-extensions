<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Exception;

class InvalidEnumException extends \InvalidArgumentException
{
    /**
     * @param class-string<\UnitEnum>|string $enumClass
     */
    public function __construct(
        public readonly string $enumClass,
        public readonly mixed $data,
        public readonly ?string $propertyPath,
    ) {
        $data = match (true) {
            \is_object($data) => $data::class,
            \is_array($data) => 'array',
            \is_resource($data) => 'resource',
            default => (string) $data,
        };

        parent::__construct(\sprintf('Invalid enum value for %s: %s', $enumClass, $data));
    }
}
