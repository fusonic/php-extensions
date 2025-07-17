<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Infrastructure\Resolver;

use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Fusonic\FrameworkBundle\Infrastructure\Resolver\UuidEntityIdValueResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Factory\UlidFactory;
use Symfony\Component\Uid\Uuid;

/**
 * The code of this test class has been copied over from Symfony's {@see UidValueResolver} test class and was adjusted
 * to fit the needs of the custom {@see UuidEntityId} implementation.
 */
final class UuidEntityIdValueResolverTest extends TestCase
{
    #[DataProvider('provideSupports')]
    public function testSupports(bool $expected, Request $request, ArgumentMetadata $argument): void
    {
        self::assertCount((int) $expected, (new UuidEntityIdValueResolver())->resolve($request, $argument));
    }

    /**
     * @return \Iterator<string, array<mixed>>
     */
    public static function provideSupports(): \Iterator
    {
        $uuidEntityId = new readonly class extends UuidEntityId {};

        yield 'Variadic argument' => [
            false,
            new Request(query: [], request: [], attributes: ['foo' => (string) $uuidEntityId]),
            new ArgumentMetadata(
                name: 'foo',
                type: $uuidEntityId::class,
                isVariadic: true,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'No attribute for argument' => [
            false,
            new Request(query: [], request: [], attributes: []),
            new ArgumentMetadata(
                name: 'foo',
                type: $uuidEntityId::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'Attribute is not a string' => [
            false,
            new Request(query: [], request: [], attributes: ['foo' => ['bar']]),
            new ArgumentMetadata(
                name: 'foo',
                type: $uuidEntityId::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'Argument has no type' => [
            false,
            new Request(query: [], request: [], attributes: ['foo' => (string) $uuidEntityId]),
            new ArgumentMetadata(
                name: 'foo',
                type: null,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'Argument type is not a class' => [
            false,
            new Request(query: [], request: [], attributes: ['foo' => (string) $uuidEntityId]),
            new ArgumentMetadata(
                name: 'foo',
                type: 'string',
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'Argument type is not a subclass of AbstractUid' => [
            false,
            new Request(query: [], request: [], attributes: ['foo' => (string) $uuidEntityId]),
            new ArgumentMetadata(
                name: 'foo',
                type: UlidFactory::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'Abstract UuidEntityId is not supported' => [
            false,
            new Request(query: [], request: [], attributes: ['foo' => (string) $uuidEntityId]),
            new ArgumentMetadata(
                name: 'foo',
                type: UuidEntityId::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'Known subclass' => [
            true,
            new Request(query: [], request: [], attributes: ['foo' => (string) $uuidEntityId]),
            new ArgumentMetadata(
                name: 'foo',
                type: $uuidEntityId::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
        yield 'Format does not matter' => [
            true,
            new Request(query: [], request: [], attributes: ['foo' => (string) Uuid::v4()]),
            new ArgumentMetadata(
                name: 'foo',
                type: $uuidEntityId::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            ),
        ];
    }

    #[DataProvider('provideResolve')]
    public function testResolve(UuidEntityId $expected, string $requestUuid): void
    {
        self::assertEqualsCanonicalizing(
            [$expected],
            (new UuidEntityIdValueResolver())->resolve(
                request: new Request(query: [], request: [], attributes: ['id' => $requestUuid]),
                argument: new ArgumentMetadata(
                    name: 'id',
                    type: $expected::class,
                    isVariadic: false,
                    hasDefaultValue: false,
                    defaultValue: null,
                )
            )
        );
    }

    /**
     * @return \Iterator<string, array<mixed>>
     */
    public static function provideResolve(): \Iterator
    {
        $uuidEntityId = new readonly class extends UuidEntityId {};

        yield 'UUID as string' => [
            $uuidEntityId,
            (string) $uuidEntityId,
        ];
        yield 'UUID as Rfc4122 string' => [
            $uuidEntityId,
            $uuidEntityId->getValue()->toRfc4122(),
        ];
        yield 'UUID as base58 string' => [
            $uuidEntityId,
            $uuidEntityId->getValue()->toBase58(),
        ];
        yield 'UUID as base32 string' => [
            $uuidEntityId,
            $uuidEntityId->getValue()->toBase32(),
        ];
    }

    public function testResolveInvalidUuid(): void
    {
        // arrange
        $uuidEntityId = new readonly class extends UuidEntityId {};

        // assert
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The UUID for the "id" parameter is invalid.');

        // act
        (new UuidEntityIdValueResolver())->resolve(
            request: new Request(query: [], request: [], attributes: ['id' => 'foobar']),
            argument: new ArgumentMetadata(
                name: 'id',
                type: $uuidEntityId::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
            )
        );
    }
}
