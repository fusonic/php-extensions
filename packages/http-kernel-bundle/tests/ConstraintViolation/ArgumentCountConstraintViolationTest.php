<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\ConstraintViolation;

use Fusonic\HttpKernelBundle\ConstraintViolation\ArgumentCountConstraintViolation;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Normalizer\ConstraintViolationExceptionNormalizer;
use Fusonic\HttpKernelBundle\Tests\Dto\DummyClassA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;

class ArgumentCountConstraintViolationTest extends TestCase
{
    public function testMessage(): void
    {
        $error = null;

        try {
            $testClassName = DummyClassA::class;

            // Instantiate class without required arguments to trigger the exception
            // @phpstan-ignore-next-line
            new $testClassName();
        } catch (\ArgumentCountError $ex) {
            $error = $ex;
        }

        self::assertNotNull($error);

        $constraintViolation = new ArgumentCountConstraintViolation($error);

        $normalizer = new ConstraintViolationExceptionNormalizer(new ConstraintViolationListNormalizer());
        $result = $normalizer->normalize(ConstraintViolationException::fromConstraintViolation($constraintViolation));

        self::assertSame('https://symfony.com/errors/validation', $result['type']);
        self::assertSame('Validation Failed', $result['title']);
        self::assertSame('requiredArgument: This value should not be null.', $result['detail']);
        self::assertSame('requiredArgument', $result['violations'][0]['propertyPath']);
        self::assertSame('This value should not be null.', $result['violations'][0]['title']);
        self::assertSame('This value should not be null.', $result['violations'][0]['template']);
        self::assertSame([], $result['violations'][0]['parameters']);
        self::assertSame('urn:uuid:ad32d13f-c3d4-423b-909a-857b961eb720', $result['violations'][0]['type']);
        self::assertSame('IS_NULL_ERROR', $result['violations'][0]['errorName']);
    }
}
