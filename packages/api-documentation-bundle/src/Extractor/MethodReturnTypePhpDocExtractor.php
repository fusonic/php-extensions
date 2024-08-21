<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Extractor;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\PropertyInfo\Util\PhpDocTypeHelper;

/**
 * Based on {@see PhpDocExtractor}. Extracts only method return types and only uses accessors for detection.
 */
final class MethodReturnTypePhpDocExtractor implements PropertyTypeExtractorInterface
{
    private PhpDocTypeHelper $phpDocTypeHelper;

    /**
     * @var Context[]
     */
    private array $contexts = [];

    /**
     * @var array<string, DocBlock|null>
     */
    private array $docBlocks = [];

    private ContextFactory $contextFactory;
    private DocBlockFactoryInterface $docBlockFactory;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
        $this->phpDocTypeHelper = new PhpDocTypeHelper();
        $this->contextFactory = new ContextFactory();
    }

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
        $docBlock = $this->getDocBlock($class, $property);

        if (null === $docBlock) {
            return null;
        }

        $parentClass = null;
        $types = [];

        /** @var DocBlock\Tags\Var_|DocBlock\Tags\Return_|DocBlock\Tags\Param|InvalidTag|null $tag */
        foreach ($docBlock->getTagsByName('return') as $tag) {
            if (null !== $tag && !$tag instanceof InvalidTag && null !== $tag->getType()) {
                foreach ($this->phpDocTypeHelper->getTypes($tag->getType()) as $type) {
                    switch ($type->getClassName()) {
                        case 'self':
                        case 'static':
                            $resolvedClass = $class;
                            break;

                        case 'parent':
                            if (false !== $resolvedClass = $parentClass ?? $parentClass = get_parent_class($class)) {
                                break;
                            }
                            // no break

                        default:
                            $types[] = $type;
                            continue 2;
                    }

                    $types[] = new Type(Type::BUILTIN_TYPE_OBJECT, $type->isNullable(), $resolvedClass, $type->isCollection(), $type->getCollectionKeyTypes(), $type->getCollectionValueTypes());
                }
            }
        }

        if (!isset($types[0])) {
            return null;
        }

        return $types;
    }

    private function getDocBlock(string $class, string $property): ?DocBlock
    {
        $propertyHash = \sprintf('%s::%s', $class, $property);

        if (isset($this->docBlocks[$propertyHash])) {
            return $this->docBlocks[$propertyHash];
        }

        return $this->docBlocks[$propertyHash] = $this->getDocBlockFromMethod($class, $property);
    }

    private function getDocBlockFromMethod(string $class, string $propertyName): ?DocBlock
    {
        $reflectionMethod = new \ReflectionMethod($class, $propertyName);
        $reflector = $reflectionMethod->getDeclaringClass();

        foreach ($reflector->getTraits() as $trait) {
            if ($trait->hasMethod($propertyName)) {
                return $this->getDocBlockFromMethod($trait->getName(), $propertyName);
            }
        }

        try {
            return $this->docBlockFactory->create($reflectionMethod, $this->createFromReflector($reflector));
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return null;
        }
    }

    /**
     * Prevents a lot of redundant calls to ContextFactory::createForNamespace().
     *
     * @param \ReflectionClass<object> $reflector
     */
    private function createFromReflector(\ReflectionClass $reflector): Context
    {
        $cacheKey = $reflector->getNamespaceName().':'.$reflector->getFileName();

        if (isset($this->contexts[$cacheKey])) {
            return $this->contexts[$cacheKey];
        }

        $this->contexts[$cacheKey] = $this->contextFactory->createFromReflector($reflector);

        return $this->contexts[$cacheKey];
    }
}
