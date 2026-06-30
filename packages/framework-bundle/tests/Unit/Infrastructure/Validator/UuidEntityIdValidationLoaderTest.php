<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Tests\Unit\Infrastructure\Validator;

use Fusonic\FrameworkBundle\Infrastructure\Validator\UuidEntityIdValidationLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class UuidEntityIdValidationLoaderTest extends TestCase
{
    public function testItAddsMonotonicUuidV7ConstraintToUuidEntityIdProperties(): void
    {
        $metadata = new ClassMetadata(TestDto::class);

        $loaded = (new UuidEntityIdValidationLoader())->loadClassMetadata($metadata);

        self::assertTrue($loaded);

        foreach (['id', 'optionalId'] as $property) {
            $constraints = $this->getPropertyConstraints($metadata, $property);

            self::assertCount(1, $constraints, \sprintf('Property "%s" should have exactly one constraint.', $property));
            self::assertInstanceOf(Uuid::class, $constraints[0]);
            self::assertContains(Uuid::V7_MONOTONIC, $constraints[0]->versions);
        }
    }

    public function testItIgnoresPropertiesThatAreNotUuidEntityIds(): void
    {
        $metadata = new ClassMetadata(TestDto::class);

        (new UuidEntityIdValidationLoader())->loadClassMetadata($metadata);

        self::assertFalse($metadata->hasPropertyMetadata('name'));
    }

    public function testItDoesNotAddASecondUuidConstraintWhenOneIsAlreadyDeclared(): void
    {
        $metadata = new ClassMetadata(TestDto::class);
        // Simulates a manual #[Assert\Uuid] already loaded by the attribute loader earlier in the chain.
        $metadata->addPropertyConstraint('id', new Uuid());

        (new UuidEntityIdValidationLoader())->loadClassMetadata($metadata);

        $constraints = $this->getPropertyConstraints($metadata, 'id');

        self::assertCount(1, $constraints);
        self::assertInstanceOf(Uuid::class, $constraints[0]);
    }

    public function testItKeepsUnrelatedConstraintsAndStillAddsTheUuidConstraint(): void
    {
        $metadata = new ClassMetadata(TestDto::class);
        $metadata->addPropertyConstraint('id', new NotBlank());

        (new UuidEntityIdValidationLoader())->loadClassMetadata($metadata);

        $constraints = $this->getPropertyConstraints($metadata, 'id');

        self::assertCount(2, $constraints);
        self::assertCount(1, array_filter($constraints, static fn (Constraint $c): bool => $c instanceof NotBlank));
        self::assertCount(1, array_filter($constraints, static fn (Constraint $c): bool => $c instanceof Uuid));
    }

    public function testItReturnsFalseWhenThereIsNothingToLoad(): void
    {
        $metadata = new ClassMetadata(self::class);

        self::assertFalse((new UuidEntityIdValidationLoader())->loadClassMetadata($metadata));
    }

    /**
     * @return list<Constraint>
     */
    private function getPropertyConstraints(ClassMetadata $metadata, string $property): array
    {
        $constraints = [];

        foreach ($metadata->getPropertyMetadata($property) as $propertyMetadata) {
            foreach ($propertyMetadata->getConstraints() as $constraint) {
                $constraints[] = $constraint;
            }
        }

        return $constraints;
    }
}
