<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\AnnotationBuilder;

use Fusonic\ApiDocumentationBundle\Attribute\DocumentedRoute;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Type;

final class AnnotationBuilder
{
    private readonly bool $outputIsBuiltinType;
    private readonly ?Model $outputModel;
    private readonly PropertyExtractor $propertyExtractor;
    private bool $outputIsCollection;
    private ?string $output = null;
    private ?string $input = null;

    /**
     * @param \ReflectionClass<object>|null $requestObjectReflectionClass
     */
    public function __construct(
        private readonly DocumentedRoute $route,
        private readonly \ReflectionMethod $method,
        private readonly ?\ReflectionClass $requestObjectReflectionClass
    ) {
        $this->propertyExtractor = new PropertyExtractor();

        $this->configureInputType($this->route->getInput());

        $this->outputIsCollection = $this->route->getOutputIsCollection() ?? false;
        $this->configureOutputType($this->route->getOutput());

        $this->outputIsBuiltinType = in_array($this->output, Type::$builtinTypes, true);

        if (!$this->outputIsBuiltinType && null !== $this->output) {
            $this->outputModel = new Model(type: $this->output);
        }
    }

    private function configureInputType(?string $input): void
    {
        $this->input = null;

        if (null !== $input) {
            $this->input = $input;

            return;
        }

        if (null === $this->requestObjectReflectionClass) {
            return;
        }

        foreach ($this->method->getParameters() as $reflectionParameter) {
            $reflectionType = $reflectionParameter->getType();

            if (!$reflectionType instanceof \ReflectionNamedType) {
                continue;
            }

            /** @var class-string $typeName */
            $typeName = $reflectionType->getName();

            if ($reflectionType->isBuiltin()) {
                continue;
            }

            $requestObjectClassAttribute = $reflectionParameter->getAttributes(
                $this->requestObjectReflectionClass->getName()
            );

            if (count($requestObjectClassAttribute) > 0) {
                $this->input = $typeName;

                return;
            }

            $parameterReflectionClass = new \ReflectionClass($typeName);
            if ($this->requestObjectReflectionClass->isInterface()
                && $parameterReflectionClass->implementsInterface($this->requestObjectReflectionClass->getName())
            ) {
                $this->input = $typeName;

                return;
            }

            if ($parameterReflectionClass->isSubclassOf($this->requestObjectReflectionClass)) {
                $this->input = $typeName;

                return;
            }
        }
    }

    private function configureOutputType(?string $output): void
    {
        if (null !== $output) {
            $this->output = $output;

            return;
        }

        $returnType = $this->propertyExtractor->extractMethodReturnType($this->method);

        if (null === $returnType) {
            return;
        }

        $collectionType = $this->propertyExtractor->extractCollectionReturnType($returnType);

        if (null !== $collectionType) {
            $output = $collectionType->getClassName() ?? $collectionType->getBuiltinType();

            // Ignore Symfony Response objects since they cannot be
            // rendered in the docs. If a controller returns a Response,
            // an `output` can be specified to display the model.
            if (Response::class === $output || is_subclass_of($output, Response::class)) {
                return;
            }

            $this->output = $output;
            $this->outputIsCollection = true;

            return;
        }

        $output = $returnType->getClassName() ?? $returnType->getBuiltinType();

        if (Response::class === $output || is_subclass_of($output, Response::class)) {
            return;
        }

        $this->output = $output;
    }

    public function getInputAnnotation(string $httpMethod): ?AbstractAnnotation
    {
        if (null === $this->input) {
            return null;
        }

        /** @var class-string $input */
        $input = $this->input;
        $inputModel = new Model(type: $input);

        if ('get' === strtolower($httpMethod)) {
            $inputClassBasename = (new \ReflectionClass($input))->getShortName();
            $propertyInfoProperties = $this->propertyExtractor->extractClassProperties($input);

            if (null !== $propertyInfoProperties && count($propertyInfoProperties) > 0) {
                return new OA\Parameter([
                    'name' => $inputClassBasename,
                    'in' => 'query',
                    'explode' => true,
                    'value' => $inputModel,
                ]);
            }
        }

        return new OA\RequestBody([
            'value' => $inputModel,
            'required' => true,
        ]);
    }

    public function getOutputAnnotation(string $httpMethod): ?AbstractAnnotation
    {
        if (null === $this->output) {
            return null;
        }

        $statusCode = $this->route->getStatusCode();
        $description = $this->outputIsBuiltinType ? $this->builtinTypeDescription($httpMethod) : $this->classTypeDescription($httpMethod);
        $options = [
            'response' => (string) ($statusCode ?? 200),
            'description' => $description,
        ];

        if ($this->outputIsCollection) {
            $items = $this->outputIsBuiltinType ? ['type' => $this->output] : new OA\Items(['ref' => $this->outputModel]);
            $options['value'] = new OA\JsonContent([
                'type' => 'array',
                'items' => $items,
            ]);

            return new OA\Response($options);
        }

        $options['value'] = $this->outputIsBuiltinType ? new OA\JsonContent(['type' => $this->output]) : $this->outputModel;

        return new OA\Response($options);
    }

    private function builtinTypeDescription(string $httpMethod): string
    {
        return sprintf(
            '%s %s%s',
            $httpMethod,
            $this->output,
            $this->outputIsCollection ? ' collection' : ''
        );
    }

    private function classTypeDescription(string $httpMethod): string
    {
        /** @var class-string $output */
        $output = $this->output;
        $outputClassBasename = (new \ReflectionClass($output))->getShortName();

        return sprintf(
            '%s %s%s',
            $httpMethod,
            $outputClassBasename,
            $this->outputIsCollection ? ' collection' : ''
        );
    }
}
