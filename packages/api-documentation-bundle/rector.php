<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\Symfony\CodeQuality\Rector\Class_\ControllerMethodInjectionToConstructorRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPHPStanConfigs([__DIR__.'/phpstan.neon'])
    ->withPhpSets(php82: true)
    ->withComposerBased(
        phpunit: true,
        symfony: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        phpunitCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withSkip([
        ControllerMethodInjectionToConstructorRector::class => [
            __DIR__.'/tests/App/Controller/TestTaggedController.php',
        ],
        FlipTypeControlToUseExclusiveTypeRector::class,
        PreferPHPUnitThisCallRector::class,
        RemoveEmptyClassMethodRector::class => [
            __DIR__.'/tests/App/Controller/TestController.php',
        ],
    ]);
