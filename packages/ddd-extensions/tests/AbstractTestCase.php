<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\TraceableMessageBus;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @return Stub&AbstractPlatform
     */
    protected function getDatabasePlatformStub(): Stub
    {
        return self::createStub(AbstractPlatform::class);
    }

    /**
     * @return Stub&EntityManagerInterface
     */
    protected function getEntityManager(): Stub
    {
        return self::createStub(EntityManagerInterface::class);
    }

    protected function getMessageBus(): TraceableMessageBus
    {
        return new TraceableMessageBus(new MessageBus());
    }
}
