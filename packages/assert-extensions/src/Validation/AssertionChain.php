<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\AssertExtensions\Validation;

use Assert\AssertionChain as BaseAssertionChain;
use Assert\LazyAssertionException;
use Fusonic\AssertExtensions\Exception\AssertionFailedException;

class AssertionChain extends BaseAssertionChain
{
    /**
     * @var AssertionFailedException[]
     */
    protected array $errors = [];

    public function __construct(
        mixed $value,
        string $rootPath,
        string $propertyPath,
        mixed $defaultMessage = null,
        protected readonly bool $collectErrors = false
    ) {
        parent::__construct($value, $defaultMessage, sprintf('%s.%s', $rootPath, $propertyPath));
    }

    public function __call($methodName, $args): BaseAssertionChain
    {
        try {
            return BaseAssertionChain::__call($methodName, $args);
        } catch (LazyAssertionException $ex) {
            if ($this->collectErrors) {
                $this->errors[] = $ex;

                return $this;
            }

            throw $ex;
        }
    }

    public function verifyNow(): bool
    {
        if ($this->errors) {
            throw AssertionFailedException::fromErrors($this->errors);
        }

        return true;
    }
}
