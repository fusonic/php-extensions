<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertCountWithZeroToAssertEmptyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPHPStanConfigs([__DIR__.'/phpstan.neon'])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        strictBooleans: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withSets([
        // Doctrine
        DoctrineSetList::DOCTRINE_DBAL_30,
    ])
    ->withSkip([
        // https://github.com/rectorphp/rector-phpunit/pull/467
        AssertCountWithZeroToAssertEmptyRector::class,
        PreferPHPUnitThisCallRector::class,
    ]);
