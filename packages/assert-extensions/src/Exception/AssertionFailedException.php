<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\AssertExtensions\Exception;

use Assert\InvalidArgumentException;
use Assert\LazyAssertionException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AssertionFailedException extends LazyAssertionException
{
    private ConstraintViolationListInterface $constraintViolationList;

    /**
     * @param InvalidArgumentException[] $errors
     */
    public function __construct(string $message, array $errors)
    {
        $this->constraintViolationList = new ConstraintViolationList([]);

        foreach ($errors as $error) {
            $parameters = [];

            foreach ($error->getConstraints() as $key => $value) {
                $parameters[sprintf('{{ %s }}', $key)] = $value;
            }

            $root = null;
            $propertyPath = $error->getPropertyPath();

            if (null !== $propertyPath) {
                $pathParts = explode('.', $propertyPath);

                $root = array_shift($pathParts);
                $propertyPath = implode('.', $pathParts);
            }

            $this->constraintViolationList->add(
                new ConstraintViolation(
                    $error->getMessage(),
                    $error->getMessage(),
                    $parameters,
                    $root,
                    $propertyPath,
                    $error->getValue(),
                    null,
                    null,
                    null
                )
            );
        }
        parent::__construct(trim($message), $errors);
    }

    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
