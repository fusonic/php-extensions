<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Validation;

use Assert\AssertionChain as BaseAssertionChain;

class AssertionChain extends BaseAssertionChain
{
    protected string $rootPath;

    public function __construct(mixed $value, string $rootPath, string $propertyPath, mixed $defaultMessage = null)
    {
        parent::__construct($value, $defaultMessage, sprintf('%s.%s', $rootPath, $propertyPath));
    }
}
