<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App;

use Fusonic\ApiDocumentationBundle\FusonicApiDocumentationBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new NelmioApiDocBundle(),
            new FusonicApiDocumentationBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.php');
    }
}
