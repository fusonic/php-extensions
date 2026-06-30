<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\Validator;

use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

/**
 * Automatically applies a {@see Uuid} constraint - restricted to monotonic UUID v7 - to every property typed as a
 * {@see UuidEntityId} subclass. This removes the need to annotate each ID property with
 * #[Assert\Uuid(versions: Assert\Uuid::V7_MONOTONIC)] by hand.
 *
 * The loader runs as part of the validator's loader chain, after the attribute loader, so properties that already
 * declare their own {@see Uuid} constraint are left untouched - an explicit annotation always wins over the automatic
 * one.
 */
final class UuidEntityIdValidationLoader implements LoaderInterface
{
    public function loadClassMetadata(ClassMetadata $metadata): bool
    {
        $loaded = false;

        foreach ($metadata->getReflectionClass()->getProperties() as $property) {
            $propertyName = $property->getName();

            if (!$this->isUuidEntityId($property->getType()) || $this->hasUuidConstraint($metadata, $propertyName)) {
                continue;
            }

            $metadata->addPropertyConstraint($propertyName, new Uuid(versions: Uuid::V7_MONOTONIC));
            $loaded = true;
        }

        return $loaded;
    }

    private function isUuidEntityId(?\ReflectionType $type): bool
    {
        $types = match (true) {
            $type instanceof \ReflectionNamedType => [$type],
            $type instanceof \ReflectionUnionType, $type instanceof \ReflectionIntersectionType => $type->getTypes(),
            default => [],
        };

        foreach ($types as $namedType) {
            if ($namedType instanceof \ReflectionNamedType
                && !$namedType->isBuiltin()
                && is_subclass_of($namedType->getName(), UuidEntityId::class)
            ) {
                return true;
            }
        }

        return false;
    }

    private function hasUuidConstraint(ClassMetadata $metadata, string $propertyName): bool
    {
        if (!$metadata->hasPropertyMetadata($propertyName)) {
            return false;
        }

        foreach ($metadata->getPropertyMetadata($propertyName) as $propertyMetadata) {
            foreach ($propertyMetadata->getConstraints() as $constraint) {
                if ($constraint instanceof Uuid) {
                    return true;
                }
            }
        }

        return false;
    }
}
