<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\AnnotationBuilder;

use Fusonic\ApiDocumentationBundle\Exception\UnsupportedTypeException;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\TypeInfo\Type\WrappingTypeInterface;
use Symfony\Component\TypeInfo\TypeContext\TypeContextFactory;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\TypeInfo\TypeResolver\PhpDocAwareReflectionTypeResolver;
use Symfony\Component\TypeInfo\TypeResolver\ReflectionReturnTypeResolver;
use Symfony\Component\TypeInfo\TypeResolver\ReflectionTypeResolver;
use Symfony\Component\TypeInfo\TypeResolver\StringTypeResolver;

final readonly class PropertyExtractor
{
    private ReflectionExtractor $propertyListExtractor;
    private PhpDocAwareReflectionTypeResolver $returnTypeResolver;

    public function __construct()
    {
        $this->propertyListExtractor = new ReflectionExtractor();

        $stringTypeResolver = new StringTypeResolver();
        $typeContextFactory = new TypeContextFactory($stringTypeResolver);

        $this->returnTypeResolver = new PhpDocAwareReflectionTypeResolver(
            new ReflectionReturnTypeResolver(new ReflectionTypeResolver(), $typeContextFactory),
            $stringTypeResolver,
            $typeContextFactory
        );
    }

    /**
     * @param class-string $className
     *
     * @return string[]
     */
    public function extractClassProperties(string $className): array
    {
        $properties = $this->propertyListExtractor->getProperties($className) ?? [];
        $propertiesWithoutIgnoreAttribute = [];

        $reflectionClass = new \ReflectionClass($className);

        foreach ($properties as $property) {
            $reflectionProperty = $reflectionClass->getProperty($property);
            $attributes = $reflectionProperty->getAttributes(Ignore::class);

            if ([] === $attributes) {
                $propertiesWithoutIgnoreAttribute[] = $property;
            }
        }

        return $propertiesWithoutIgnoreAttribute;
    }

    public function extractMethodReturnType(\ReflectionMethod $method): ?Type
    {
        try {
            $returnType = $this->returnTypeResolver->resolve($method);
        } catch (UnsupportedException) {
            // The method has no return type declaration at all, or declares one that symfony/type-info cannot model.
            return null;
        }

        $returnType = $this->unwrapNullable($returnType);

        if ($returnType instanceof UnionType) {
            throw new UnsupportedTypeException('Multiple return types not supported');
        }

        return $returnType;
    }

    public function extractCollectionReturnType(Type $returnType): ?Type
    {
        $returnType = $this->unwrapNullable($returnType);

        if ($returnType instanceof CollectionType) {
            $valueType = $this->unwrapNullable($returnType->getCollectionValueType());

            // An untyped `array` resolves to a collection of `mixed`.
            if ($valueType instanceof BuiltinType && $valueType->isIdentifiedBy(TypeIdentifier::MIXED)) {
                return null;
            }

            return $this->assertSingleType($valueType);
        }

        // A generic return type such as `Foo<Bar>` is documented as a collection of `Bar`, with the `Foo` wrapper
        // discarded. See commit c44bf5b.
        if ($returnType instanceof GenericType) {
            $variableTypes = $returnType->getVariableTypes();

            if ([] === $variableTypes) {
                return null;
            }

            // For a two-parameter generic such as `Foo<TKey, TValue>` the documented type is the value, i.e. the last argument.
            return $this->assertSingleType($this->unwrapNullable($variableTypes[\count($variableTypes) - 1]));
        }

        return null;
    }

    public function getTypeName(Type $type): ?string
    {
        // EnumType and BackedEnumType extend ObjectType, so they are covered here too.
        if ($type instanceof ObjectType) {
            return $type->getClassName();
        }

        if ($type instanceof BuiltinType) {
            return $type->getTypeIdentifier()->value;
        }

        if ($type instanceof WrappingTypeInterface) {
            return $this->getTypeName($type->getWrappedType());
        }

        return null;
    }

    public function isBuiltinTypeName(string $name): bool
    {
        return null !== TypeIdentifier::tryFrom($name);
    }

    public function isNonDocumentableTypeName(string $name): bool
    {
        return \in_array(
            needle: $name,
            haystack: [
                TypeIdentifier::VOID->value,
                TypeIdentifier::NULL->value,
                TypeIdentifier::NEVER->value,
                TypeIdentifier::MIXED->value,
            ],
            strict: true
        );
    }

    private function unwrapNullable(Type $type): Type
    {
        return $type instanceof NullableType ? $type->getWrappedType() : $type;
    }

    private function assertSingleType(Type $type): Type
    {
        if ($type instanceof UnionType) {
            throw new UnsupportedTypeException('Multiple collection return types not supported');
        }

        return $type;
    }
}
