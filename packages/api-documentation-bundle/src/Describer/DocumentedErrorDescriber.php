<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Describer;

use Fusonic\ApiDocumentationBundle\AnnotationBuilder\PropertyExtractor;
use Fusonic\ApiDocumentationBundle\Attribute\DocumentedError;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

final readonly class DocumentedErrorDescriber
{
    /**
     * @var class-string|null
     */
    private ?string $exceptionContextClass;

    /**
     * @param class-string|null $exceptionContextClass
     */
    public function __construct(
        ?string $exceptionContextClass = null,
        private ?string $exceptionContextMethod = null,
    ) {
        if (null !== $exceptionContextClass
            && !class_exists($exceptionContextClass)
            && !interface_exists($exceptionContextClass)
        ) {
            throw new \InvalidArgumentException(\sprintf('Class %s does not exist.', $exceptionContextClass));
        }

        $this->exceptionContextClass = $exceptionContextClass;
    }

    /**
     * @return OA\Response[]
     */
    public function buildResponseAnnotations(\ReflectionMethod $method, string $httpMethod): array
    {
        $responses = [];

        foreach ($this->getDocumentedErrors($method) as $documentedError) {
            if ([] !== $documentedError->methods && !\in_array($httpMethod, $documentedError->methods, true)) {
                continue;
            }

            $responseOptions = [
                'response' => (string) $documentedError->statusCode,
                'description' => $documentedError->description,
            ];

            $contextMethod = $this->resolveExceptionContextMethod($documentedError->exceptionClass);

            if (null !== $contextMethod) {
                $responseOptions['value'] = $this->buildContextResponseContent($contextMethod);
            }

            $responses[] = new OA\Response($responseOptions);
        }

        return $responses;
    }

    private function resolveExceptionContextMethod(string $exceptionClass): ?\ReflectionMethod
    {
        if (null === $this->exceptionContextClass || null === $this->exceptionContextMethod) {
            return null;
        }

        if (!is_a($exceptionClass, $this->exceptionContextClass, true)
            || !method_exists($exceptionClass, $this->exceptionContextMethod)
        ) {
            return null;
        }

        return new \ReflectionMethod($exceptionClass, $this->exceptionContextMethod);
    }

    /**
     * @return DocumentedError[]
     */
    private function getDocumentedErrors(\ReflectionMethod $method): array
    {
        return array_map(
            static fn (\ReflectionAttribute $a): DocumentedError => $a->newInstance(),
            $method->getAttributes(DocumentedError::class)
        );
    }

    private function buildContextResponseContent(\ReflectionMethod $contextMethod): OA\JsonContent|Model
    {
        $extractor = new PropertyExtractor();
        $returnType = $extractor->extractMethodReturnType($contextMethod);

        if (null === $returnType) {
            return new OA\JsonContent(['type' => 'object']);
        }

        $collectionType = $extractor->extractCollectionReturnType($returnType);

        if (null !== $collectionType) {
            $itemType = $collectionType->getClassName() ?? $collectionType->getBuiltinType();

            if (null !== $collectionType->getClassName()) {
                return new OA\JsonContent([
                    'type' => 'array',
                    'items' => new OA\Items(['ref' => new Model(type: $collectionType->getClassName())]),
                ]);
            }

            return new OA\JsonContent([
                'type' => 'array',
                'items' => new OA\Items(['type' => $itemType]),
            ]);
        }

        $className = $returnType->getClassName();

        if (null !== $className && !\in_array($className, Type::$builtinTypes, true)) {
            return new Model(type: $className);
        }

        return new OA\JsonContent(['type' => $returnType->getBuiltinType() ?? 'object']);
    }
}
