<?php

namespace Fusonic\DDDExtensions\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\TraceableMessageBus;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @return MockObject&AbstractPlatform
     */
    protected function getDatabasePlatformStub(): MockObject
    {
        return $this->createMock(AbstractPlatform::class);
    }

    /**
     * @return MockObject&EntityManagerInterface
     */
    protected function getEntityManager(): MockObject
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    protected function getMessageBus(): TraceableMessageBus
    {
        return new TraceableMessageBus(new MessageBus());
    }
}
