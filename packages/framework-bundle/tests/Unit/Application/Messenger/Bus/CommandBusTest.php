<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Application\Messenger\Bus;

use Fusonic\FrameworkBundle\Application\Messenger\Bus\CommandBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * This test class verifies that the {@see CommandBus} implementation correctly delegates to the
 * {@see MessageBusInterface}.
 *
 * It ensures that:
 *  - Commands are dispatched without modification
 *  - Stamps are correctly passed to the underlying bus
 *  - Results from {@see HandledStamp}s are properly extracted
 */
final class CommandBusTest extends TestCase
{
    public function testDispatchCommand(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [])
            ->willReturn(Envelope::wrap(new \stdClass()));

        $commandBus = new CommandBus(bus: $messageBus);

        // act
        $resultEnvelope = $commandBus->dispatch(new \stdClass());

        // assert
        self::assertInstanceOf(\stdClass::class, $resultEnvelope->getMessage());
        self::assertCount(0, $resultEnvelope->all());
    }

    public function testDispatchCommandWithStamps(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [new DispatchAfterCurrentBusStamp()])
            ->willReturn(Envelope::wrap(message: new \stdClass(), stamps: [new DispatchAfterCurrentBusStamp()]));

        $commandBus = new CommandBus(bus: $messageBus);

        // act
        $resultEnvelope = $commandBus->dispatch(
            message: new \stdClass(),
            stamps: [new DispatchAfterCurrentBusStamp()]
        );

        // assert
        self::assertInstanceOf(\stdClass::class, $resultEnvelope->getMessage());
        self::assertCount(1, $resultEnvelope->all());
        self::assertCount(1, $resultEnvelope->all(DispatchAfterCurrentBusStamp::class));
    }

    public function testDispatchCommandAndGetResult(): void
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

        $commandBus = new CommandBus(bus: $messageBus);

        // act
        $result = $commandBus->dispatchAndGetResult(new \stdClass());

        // assert
        self::assertInstanceOf(\stdClass::class, $result);
    }

    public function testDispatchCommandAndGetResultWithNoResult(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [])
            ->willReturn(Envelope::wrap(new \stdClass()));

        $commandBus = new CommandBus(bus: $messageBus);

        // act
        $result = $commandBus->dispatchAndGetResult(new \stdClass());

        // assert
        self::assertNull($result);
    }

    public function testDispatchCommandAndGetResultWithStamps(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [new DispatchAfterCurrentBusStamp()])
            ->willReturn(
                Envelope::wrap(
                    message: new \stdClass(),
                    stamps: [
                        new HandledStamp(result: new \stdClass(), handlerName: 'handler'),
                    ]
                )
            );

        $commandBus = new CommandBus(bus: $messageBus);

        // act
        $result = $commandBus->dispatchAndGetResult(
            message: new \stdClass(),
            stamps: [new DispatchAfterCurrentBusStamp()]
        );

        // assert
        self::assertInstanceOf(\stdClass::class, $result);
    }
}
