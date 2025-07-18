<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Application\Messenger\Bus;

use Fusonic\FrameworkBundle\Application\Messenger\Bus\EventBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * This test class verifies that the {@see EventBus} implementation correctly delegates to the
 * {@see MessageBusInterface}.
 *
 * It ensures that:
 *  - Events are dispatched without modification
 *  - Stamps are correctly passed to the underlying bus
 */
final class EventBusTest extends TestCase
{
    public function testDispatchEvent(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [])
            ->willReturn(Envelope::wrap(new \stdClass()));

        $eventBus = new EventBus(bus: $messageBus);

        // act
        $resultEnvelope = $eventBus->dispatch(new \stdClass());

        // assert
        self::assertInstanceOf(\stdClass::class, $resultEnvelope->getMessage());
        self::assertCount(0, $resultEnvelope->all());
    }

    public function testDispatchEventWithStamps(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [new DelayStamp(1)])
            ->willReturn(Envelope::wrap(message: new \stdClass(), stamps: [new DelayStamp(1)]));

        $eventBus = new EventBus(bus: $messageBus);

        // act
        $resultEnvelope = $eventBus->dispatch(
            message: new \stdClass(),
            stamps: [new DelayStamp(1)]
        );

        // assert
        self::assertInstanceOf(\stdClass::class, $resultEnvelope->getMessage());
        self::assertCount(1, $resultEnvelope->all());
        self::assertCount(1, $resultEnvelope->all(DelayStamp::class));
    }

    public function testDispatchEventAfterCurrentBus(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(\stdClass::class), [new DispatchAfterCurrentBusStamp()])
            ->willReturn(Envelope::wrap(message: new \stdClass(), stamps: [new DispatchAfterCurrentBusStamp()]));

        $eventBus = new EventBus(bus: $messageBus);

        // act
        $resultEnvelope = $eventBus->dispatchAfterCurrentBus(new \stdClass());

        // assert
        self::assertInstanceOf(\stdClass::class, $resultEnvelope->getMessage());
        self::assertCount(1, $resultEnvelope->all());
        self::assertCount(1, $resultEnvelope->all(DispatchAfterCurrentBusStamp::class));
    }

    public function testDispatchEventAfterCurrentBusWithStamps(): void
    {
        // arrange
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(\stdClass::class),
                [
                    new DelayStamp(1),
                    new DispatchAfterCurrentBusStamp(),
                ]
            )
            ->willReturn(
                Envelope::wrap(
                    message: new \stdClass(),
                    stamps: [
                        new DelayStamp(1),
                        new DispatchAfterCurrentBusStamp(),
                    ]
                )
            );

        $eventBus = new EventBus(bus: $messageBus);

        // act
        $resultEnvelope = $eventBus->dispatchAfterCurrentBus(new \stdClass(), [new DelayStamp(1)]);

        // assert
        self::assertInstanceOf(\stdClass::class, $resultEnvelope->getMessage());
        self::assertCount(2, $resultEnvelope->all());
        self::assertCount(1, $resultEnvelope->all(DelayStamp::class));
        self::assertCount(1, $resultEnvelope->all(DispatchAfterCurrentBusStamp::class));
    }
}
