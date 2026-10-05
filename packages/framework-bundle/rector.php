<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Configs\Rector\Closure\ServiceSettersToSettersAutodiscoveryRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NarrowObjectReturnTypeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPHPStanConfigs([__DIR__.'/phpstan.neon'])
    ->withPhpSets(php84: true)
    ->withComposerBased(
        doctrine: true,
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
        FlipTypeControlToUseExclusiveTypeRector::class,
        NarrowObjectReturnTypeRector::class => [
            // ↓ Rule breaks BC layer
            __DIR__.'/tests/Unit/Infrastructure/Doctrine/Types/UuidEntityIdTypeTest.php',
        ],
        PreferPHPUnitThisCallRector::class,
        RenameClassRector::class => [
            // ↓ Rule breaks BC layer
            __DIR__.'/tests/Unit/Infrastructure/Doctrine/Types/UuidEntityIdTypeTest.php',
        ],
        ServiceSettersToSettersAutodiscoveryRector::class,
    ]);
