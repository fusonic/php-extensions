<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Validation;

use Assert\LazyAssertion as BaseLazyAssertion;

class LazyAssertion extends BaseLazyAssertion
{
    protected string $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return $this
     */
    public function that(mixed $value, string $propertyPath = null, mixed $defaultMessage = null): static
    {
        $propertyPath = null === $propertyPath ? $this->rootPath : sprintf('%s.%s', $this->rootPath, $propertyPath);

        parent::that($value, $propertyPath, $defaultMessage);

        return $this;
    }
}
