<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Fusonic\FrameworkBundle\Infrastructure\Doctrine\Types\UuidEntityIdType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UuidEntityIdTypeTest extends TestCase
{
    private const DUMMY_UUID = '0195820d-38da-7972-adbe-474a038c4a66';

    /**
     * Simulated class extending the abstract {@see UuidEntityId} class.
     */
    private UuidEntityId $uuidEntityId;

    /**
     * Simulated type class extending the abstract {@see UuidEntityIdType} class.
     */
    private UuidEntityIdType $type;

    protected function setUp(): void
    {
        $this->uuidEntityId = new readonly class extends UuidEntityId {};

        $this->type = new class extends UuidEntityIdType {
            public const NAME = 'sample_uuid';

            /**
             * @var class-string<UuidEntityId>
             */
            private static string $uuidEntityIdClass;

            protected function getUuidEntityIdClass(): string
            {
                return self::$uuidEntityIdClass;
            }

            protected function getName(): string
            {
                return self::NAME;
            }

            /**
             * @param class-string<UuidEntityId> $uuidEntityIdClass
             */
            public static function setUuidEntityIdClass(string $uuidEntityIdClass): void
            {
                self::$uuidEntityIdClass = $uuidEntityIdClass;
            }
        };
        $this->type::setUuidEntityIdClass($this->uuidEntityId::class);
    }

    public function testUuidEntityIdConvertsToDatabaseValue(): void
    {
        // arrange
        $uuidEntityId = $this->uuidEntityId::fromString(self::DUMMY_UUID);

        // act
        $convertedDatabaseValue = $this->type->convertToDatabaseValue($uuidEntityId, new PostgreSQLPlatform());

        // assert
        self::assertSame(self::DUMMY_UUID, $convertedDatabaseValue);
    }

    public function testNullConvertsToDatabaseValue(): void
    {
        // act
        $convertedDatabaseValue = $this->type->convertToDatabaseValue(null, new PostgreSQLPlatform());

        // assert
        self::assertNull($convertedDatabaseValue);
    }

    public function testNotSupportedTypeDatabaseConversionThrowsException(): void
    {
        // assert
        $this->expectException(InvalidType::class);
        $this->expectExceptionMessage('Could not convert PHP value of type stdClass to type sample_uuid. Expected one of the following types: null, string, Fusonic\FrameworkBundle\Domain\Id\UuidEntityId.');

        // act
        $this->type->convertToDatabaseValue(new \stdClass(), new PostgreSQLPlatform());
    }

    public function testUuidStringConvertsToDatabaseValue(): void
    {
        // act
        $convertedDatabaseValue = $this->type->convertToDatabaseValue(self::DUMMY_UUID, new PostgreSQLPlatform());

        // assert
        self::assertSame(self::DUMMY_UUID, $convertedDatabaseValue);
    }

    public function testInvalidUuidStringDatabaseConversionThrowsException(): void
    {
        // arrange
        $invalidUuidString = 'abcdefg';

        // assert
        $this->expectException(ValueNotConvertible::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Could not convert database value "%s" to Doctrine Type "sample_uuid".',
                $invalidUuidString
            )
        );

        // act
        $this->type->convertToDatabaseValue($invalidUuidString, new PostgreSQLPlatform());
    }

    public function testUuidEntityIdConvertsToPhpValue(): void
    {
        // arrange
        $uuidEntityId = new $this->uuidEntityId();

        // act
        $convertedPHPValue = $this->type->convertToPHPValue($uuidEntityId, new PostgreSQLPlatform());

        // assert
        self::assertNotNull($convertedPHPValue);
        self::assertTrue($uuidEntityId->equals($convertedPHPValue));
    }

    public function testNullConvertsToPhpValue(): void
    {
        // act
        $convertedPHPValue = $this->type->convertToPHPValue(null, new SQLitePlatform());

        // assert
        self::assertNull($convertedPHPValue);
    }

    public function testUuidStringConvertsToPhpValue(): void
    {
        // act
        $convertedPHPValue = $this->type->convertToPHPValue(self::DUMMY_UUID, new SQLitePlatform());

        // assert
        self::assertInstanceOf(UuidEntityId::class, $convertedPHPValue);
        self::assertSame(self::DUMMY_UUID, $convertedPHPValue->__toString());
    }

    public function testBinaryUuidStringConvertsToPhpValue(): void
    {
        // arrange
        $binaryUuidString = Uuid::fromString(self::DUMMY_UUID)->toBinary();

        // act
        $convertedPHPValue = $this->type->convertToPHPValue($binaryUuidString, new SQLitePlatform());

        // assert
        self::assertInstanceOf(UuidEntityId::class, $convertedPHPValue);
        self::assertSame(self::DUMMY_UUID, $convertedPHPValue->__toString());
    }

    public function testNotSupportedTypePhpConversionThrowsException(): void
    {
        // arrange
        $unsupportedTypeValue = 123456;

        // assert
        $this->expectException(InvalidType::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Could not convert PHP value %d to type sample_uuid. Expected one of the following types: null, string, Fusonic\FrameworkBundle\Domain\Id\UuidEntityId.',
                $unsupportedTypeValue
            )
        );

        // act
        $this->type->convertToPHPValue($unsupportedTypeValue, new SQLitePlatform());
    }

    public function testInvalidUuidStringPhpConversionThrowsException(): void
    {
        // arrange
        $invalidUuidString = 'abcdefg';

        // assert
        $this->expectException(ValueNotConvertible::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Could not convert database value "%s" to Doctrine Type "sample_uuid".',
                $invalidUuidString
            )
        );

        // act
        $this->type->convertToPHPValue($invalidUuidString, new SQLitePlatform());
    }

    #[DataProvider('provideSqlDeclarations')]
    public function testGetSqlDeclaration(AbstractPlatform $platform, string $expectedDeclaration): void
    {
        // act
        $sqlDeclaration = $this->type->getSQLDeclaration([], $platform);

        // assert
        self::assertSame($expectedDeclaration, $sqlDeclaration);
    }

    public static function provideSqlDeclarations(): \Generator
    {
        yield [new PostgreSQLPlatform(), 'UUID'];
        yield [new SQLitePlatform(), 'CHAR(36)'];
        yield [new MySQLPlatform(), 'CHAR(36)'];
        yield [new MariaDBPlatform(), 'CHAR(36)'];
    }
}
