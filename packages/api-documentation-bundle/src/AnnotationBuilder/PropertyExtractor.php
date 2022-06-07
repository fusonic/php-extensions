<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\AnnotationBuilder;

use Fusonic\ApiDocumentationBundle\Exception\UnsupportedTypeException;
use Fusonic\ApiDocumentationBundle\Extractor\MethodReturnTypePhpDocExtractor;
use Fusonic\ApiDocumentationBundle\Extractor\MethodReturnTypeReflectionExtractor;
use ReflectionMethod;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;

final class PropertyExtractor
{
    private readonly PropertyInfoExtractor $propertyInfoExtractor;

    public function __construct()
    {
        $this->propertyInfoExtractor = new PropertyInfoExtractor(
            [new ReflectionExtractor()],
            [new MethodReturnTypePhpDocExtractor(), new MethodReturnTypeReflectionExtractor()],
        );
    }

    public function extractClassProperties(string $className): ?array
    {
        return $this->propertyInfoExtractor->getProperties($className);
    }

    public function extractMethodReturnType(ReflectionMethod $method): ?Type
    {
        $returnTypes = $this->propertyInfoExtractor->getTypes(
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        if (null !== $returnTypes && count($returnTypes) > 0) {
            if (count($returnTypes) > 1) {
                throw new UnsupportedTypeException('Multiple return types not supported');
            }

            return $returnTypes[0];
        }

        return null;
    }

    public function extractCollectionReturnType(Type $returnType): ?Type
    {
        if (!$returnType->isCollection()) {
            return null;
        }

        $collectionTypes = $returnType->getCollectionValueTypes();

        if (count($collectionTypes) > 0) {
            if (count($collectionTypes) > 1) {
                throw new UnsupportedTypeException('Multiple collection return types not supported');
            }

            return $collectionTypes[0];
        }

        return null;
    }
}
