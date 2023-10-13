<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

final class FunctionalTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testBootKernel(): void
    {
        (new Filesystem())->remove('var/cache/test');
        self::bootKernel();
        self::assertTrue(static::$booted);
    }

    public function testService(): void
    {
        $service = self::getContainer()->get('fusonic_api_documentation.describers.openapi_php.default');

        self::assertNotNull($service);
    }
}
