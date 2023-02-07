<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Validation;

use Assert\Assertion as BaseAssertion;
use Assert\LazyAssertionException;
use Fusonic\DDDExtensions\Domain\Exception\AssertionFailedException;

class Assertion extends BaseAssertion
{
    private array $errors = [];
    
    /**
     * {@inheritDoc}
     */
    protected static function createException(mixed $value, mixed $message, mixed $code, mixed $propertyPath = null, array $constraints = []): LazyAssertionException
    {
        $exception = parent::createException(
            $value,
            $message,
            $code,
            $propertyPath,
            $constraints
        );
        
        return $exception;
    }
    
    public function verifyNow(): bool
    {
        if ($this->errors) {
            throw AssertionFailedException::fromErrors($this->errors);
        }
        
        return true;
    }
}
