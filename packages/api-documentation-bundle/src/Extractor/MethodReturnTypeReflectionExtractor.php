<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Extractor;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Based on {@see ReflectionExtractor}. Extracts only method return types and only uses accessors for detection.
 */
final class MethodReturnTypeReflectionExtractor implements PropertyTypeExtractorInterface
{
    /**
     * @param array<mixed> $context
     */
    public function getType(string $class, string $property, array $context = []): ?Type
    {
        return $this->getTypes($class, $property, $context)[0] ?? null;
    }

    /**
     * @param array<mixed> $context
     */
    public function getTypes(string $class, string $property, array $context = []): ?array
    {
        return $this->extractFromAccessor($class, $property);
    }

    /**
     * @return Type[]|null
     */
    private function extractFromAccessor(string $class, string $property): ?array
    {
        $reflectionMethod = new \ReflectionMethod($class, $property);
        $reflectionType = $reflectionMethod->getReturnType();

        if (null !== $reflectionType) {
            return $this->extractFromReflectionType($reflectionType, $reflectionMethod->getDeclaringClass());
        }

        return null;
    }

    /**
     * @param \ReflectionClass<object> $declaringClass
     *
     * @return Type[]
     */
    private function extractFromReflectionType(\ReflectionType $reflectionType, \ReflectionClass $declaringClass): array
    {
        $types = [];
        $nullable = $reflectionType->allowsNull();

        foreach (
            ($reflectionType instanceof \ReflectionUnionType || $reflectionType instanceof \ReflectionIntersectionType)
                ? $reflectionType->getTypes() : [$reflectionType] as $type
        ) {
            if (!$type instanceof \ReflectionNamedType) {
                // Nested composite types are not supported yet.
                return [];
            }

            $phpTypeOrClass = $type->getName();

            if ('null' === $phpTypeOrClass || 'mixed' === $phpTypeOrClass || 'never' === $phpTypeOrClass) {
                continue;
            }

            if (Type::BUILTIN_TYPE_ARRAY === $phpTypeOrClass) {
                $types[] = new Type(Type::BUILTIN_TYPE_ARRAY, $nullable, null, true);
            } elseif ('void' === $phpTypeOrClass) {
                $types[] = new Type(Type::BUILTIN_TYPE_NULL, $nullable);
            } elseif ($type->isBuiltin()) {
                $types[] = new Type($phpTypeOrClass, $nullable);
            } else {
                $types[] = new Type(
                    Type::BUILTIN_TYPE_OBJECT,
                    $nullable,
                    $this->resolveTypeName($phpTypeOrClass, $declaringClass)
                );
            }
        }

        return $types;
    }

    /**
     * @param \ReflectionClass<object> $declaringClass
     */
    private function resolveTypeName(string $name, \ReflectionClass $declaringClass): string
    {
        if ('self' === $lcName = strtolower($name)) {
            return $declaringClass->name;
        }

        $parent = $declaringClass->getParentClass();

        if ('parent' === $lcName && false !== $parent) {
            return $parent->name;
        }

        return $name;
    }
}
