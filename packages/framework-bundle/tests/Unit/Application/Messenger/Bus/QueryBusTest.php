<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Application\Messenger\Bus;

use Fusonic\FrameworkBundle\Application\Messenger\Bus\QueryBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * This test class verifies that the {@see QueryBus} implementation correctly delegates to the
 * {@see MessageBusInterface}.
 *
 * It ensures that:
 *  - Queries are dispatched without modification
 *  - Stamps are correctly passed to the underlying bus
 *  - Results from {@see HandledStamp}s are properly extracted
 */
final class QueryBusTest extends TestCase
{
    public function testDispatchQueryAndGetResult(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [])
            ->willReturn(
                Envelope::wrap(
                    message: new \stdClass(),
                    stamps: [
                        new HandledStamp(result: new \stdClass(), handlerName: 'handler'),
                    ]
                )
            );

        $queryBus = new QueryBus(bus: $messageBus);

        // act
        $result = $queryBus->dispatchAndGetResult(new \stdClass());

        // assert
        self::assertInstanceOf(\stdClass::class, $result);
    }

    public function testDispatchQueryAndGetResultWithNoResult(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [])
            ->willReturn(Envelope::wrap(message: new \stdClass()));

        $queryBus = new QueryBus(bus: $messageBus);

        // act
        $result = $queryBus->dispatchAndGetResult(new \stdClass());

        // assert
        self::assertNull($result);
    }

    public function testDispatchQueryAndGetResultWithStamps(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [new DelayStamp(1)])
            ->willReturn(
                Envelope::wrap(
                    message: new \stdClass(),
                    stamps: [
                        new DelayStamp(1),
                        new HandledStamp(result: new \stdClass(), handlerName: 'handler'),
                    ]
                )
            );

        $queryBus = new QueryBus(bus: $messageBus);

        // act
        $result = $queryBus->dispatchAndGetResult(
            message: new \stdClass(),
            stamps: [new DelayStamp(1)]
        );

        // assert
        self::assertInstanceOf(\stdClass::class, $result);
    }
}
