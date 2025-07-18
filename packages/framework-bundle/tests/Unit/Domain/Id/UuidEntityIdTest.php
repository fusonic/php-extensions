<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Domain\Id;

use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Uid\UuidV7;

final class UuidEntityIdTest extends TestCase
{
    public function testCreateWithoutValue(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};

        // act
        $uuidEntityId = new $uuidEntityIdClass();

        // assert
        self::assertInstanceOf(UuidV7::class, $uuidEntityId->getValue());
        self::assertTrue($uuidEntityId->isDefined());
    }

    public function testCreateWithUuidV7(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};
        $uuidV7 = Uuid::v7();

        // act
        $uuidEntityId = new $uuidEntityIdClass($uuidV7);

        // assert
        self::assertInstanceOf(UuidV7::class, $uuidEntityId->getValue());
        self::assertTrue($uuidV7->equals($uuidEntityId->getValue()));
    }

    public function testCreateWithUuidV4(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};
        $uuidV4 = Uuid::v4();

        // act
        $uuidEntityId = new $uuidEntityIdClass($uuidV4);

        // assert
        self::assertInstanceOf(UuidV4::class, $uuidEntityId->getValue());
        self::assertTrue($uuidV4->equals($uuidEntityId->getValue()));
    }

    public function testCreateFromString(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};
        $uuidV7String = '0195820d-38da-7972-adbe-474a038c4a66';

        // act
        $uuidEntityId = $uuidEntityIdClass::fromString($uuidV7String);

        // assert
        self::assertInstanceOf(UuidV7::class, $uuidEntityId->getValue());
        self::assertSame($uuidV7String, $uuidEntityId->getValue()->toRfc4122());
    }
}
