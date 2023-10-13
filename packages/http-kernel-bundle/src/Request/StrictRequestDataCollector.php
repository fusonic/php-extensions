<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Request;

use Fusonic\HttpKernelBundle\Cache\ReflectionClassCache;
use Fusonic\HttpKernelBundle\Exception\ObjectTypeNotSupportedException;
use Fusonic\HttpKernelBundle\Exception\UnionTypeNotSupportedException;
use Fusonic\HttpKernelBundle\Request\BodyParser\FormRequestBodyParser;
use Fusonic\HttpKernelBundle\Request\BodyParser\JsonRequestBodyParser;
use Fusonic\HttpKernelBundle\Request\BodyParser\RequestBodyParserInterface;
use Fusonic\HttpKernelBundle\Request\UrlParser\FilterVarUrlParser;
use Fusonic\HttpKernelBundle\Request\UrlParser\UrlParserInterface;
use Fusonic\HttpKernelBundle\Types\TypeHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Type;

class StrictRequestDataCollector implements RequestDataCollectorInterface
{
    public const METHODS_WITH_STRICT_TYPE_CHECKS = [
        Request::METHOD_PUT,
        Request::METHOD_POST,
        Request::METHOD_DELETE,
        Request::METHOD_PATCH,
    ];

    /**
     * @var array<string, RequestBodyParserInterface>
     */
    private readonly array $requestBodyParsers;

    private readonly UrlParserInterface $urlParser;
    private ?PropertyInfoExtractor $propertyInfoExtractor = null;

    /**
     * @param array<string, RequestBodyParserInterface>|null $requestBodyParsers
     */
    public function __construct(
        ?UrlParserInterface $urlParser = null,

        ?array $requestBodyParsers = null,
    ) {
        $this->urlParser = $urlParser ?? new FilterVarUrlParser();
        $this->requestBodyParsers = $requestBodyParsers ?? [
            'json' => new JsonRequestBodyParser(),
            'default' => new FormRequestBodyParser(),
        ];
    }

    public function collect(Request $request, string $className): array
    {
        $routeParameters = $this->parseUrlProperties($request->attributes->get('_route_params', []), $className);

        if (\in_array($request->getMethod(), self::METHODS_WITH_STRICT_TYPE_CHECKS, true)) {
            return $this->mergeRequestData($this->parseRequestBody($request), $routeParameters);
        }

        return $this->mergeRequestData($this->parseUrlProperties($request->query->all(), $className), $routeParameters);
    }

    /**
     * @param array<mixed>         $data
     * @param array<string, mixed> $routeParameters
     *
     * @return array<string, mixed>
     */
    private function mergeRequestData(array $data, array $routeParameters): array
    {
        if (\count($keys = array_intersect_key($data, $routeParameters)) > 0) {
            throw new BadRequestHttpException(sprintf('Parameters (%s) used as route attributes can not be used in the request body or query parameters.', implode(', ', array_keys($keys))));
        }

        return array_merge($data, $routeParameters);
    }

    /**
     * @return mixed[]
     */
    private function parseRequestBody(Request $request): array
    {
        $requestBodyParser = $this->requestBodyParsers[$request->getContentTypeFormat()] ?? null;

        if (null === $requestBodyParser) {
            $requestBodyParser = $this->requestBodyParsers['default'];
        }

        return $requestBodyParser->parse($request);
    }

    /**
     * Parse the string properties of the appropriate types based on the types in the class. Since route parameters
     * and query parameters always come in as strings.
     *
     * @param class-string         $className
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function parseUrlProperties(array $params, string $className, ?string $propertyPath = null): array
    {
        $reflectionClass = ReflectionClassCache::getReflectionClass($className);

        foreach ($params as $name => $param) {
            if ($reflectionClass->hasProperty($name)) {
                $property = $reflectionClass->getProperty($name);

                /** @var \ReflectionNamedType|\ReflectionUnionType|null $propertyType */
                $propertyType = $property->getType();

                if ($propertyType instanceof \ReflectionUnionType) {
                    throw UnionTypeNotSupportedException::fromReflectionUnionType($propertyType);
                }

                /** @var string|class-string|null $type */
                $type = $propertyType?->getName();

                if ('object' === $type || !\in_array($type, Type::$builtinTypes, true)) {
                    throw new ObjectTypeNotSupportedException();
                }

                if (null !== $propertyType) {
                    if (\in_array($type, Type::$builtinTypes, true)) {
                        if (\is_string($param) || \is_array($param)) {
                            $params[$name] = $this->parseUrlProperty($className, $name, $type, $propertyType->allowsNull(), $param, $this->appendPropertyPath($propertyPath, $name));
                        }
                    }
                }
            }
        }

        return $params;
    }

    private function appendPropertyPath(?string $propertyPath, string|int $key): ?string
    {
        if (null === $propertyPath) {
            return null;
        }

        if (\is_string($key)) {
            return sprintf('%s.%s', $propertyPath, $key);
        }

        return sprintf('%s[%s]', $propertyPath, $key);
    }

    /**
     * @param class-string            $className
     * @param array<array-key, mixed> $param
     *
     * @return array<array-key, float|int|bool|string|array<array-key, mixed>|null>
     */
    private function parseArrayProperty(string $className, string $name, array $param, ?string $propertyPath = null): array
    {
        $arrayPropertyTypes = $this->getPropertyInfoExtractor()->getTypes($className, $name);

        if (null === $arrayPropertyTypes) {
            return [];
        }

        $parsedValues = [];

        foreach ($arrayPropertyTypes as $arrayPropertyType) {
            $collectionValueTypes = $arrayPropertyType->getCollectionValueTypes();

            foreach ($collectionValueTypes as $collectionValueType) {
                foreach ($param as $key => $arrayItem) {
                    if ('object' === $collectionValueType->getBuiltinType()) {
                        $collectionValueClassName = $collectionValueType->getClassName();

                        if (null !== $collectionValueClassName && TypeHelper::isUnionType($collectionValueClassName)) {
                            throw new UnionTypeNotSupportedException($collectionValueClassName);
                        }

                        throw new ObjectTypeNotSupportedException();
                    }

                    $parsedValues[$key] = $this->parseUrlProperty(
                        $className,
                        $name,
                        $collectionValueType->getBuiltinType(),
                        $collectionValueType->isNullable(),
                        $arrayItem,
                        $this->appendPropertyPath($propertyPath, $key)
                    );
                }
            }
        }

        return $parsedValues;
    }

    /**
     * @param class-string    $className
     * @param string|string[] $param
     */
    private function parseUrlProperty(string $className, string $name, string $type, bool $isNullable, string|array $param, ?string $propertyPath = null): mixed
    {
        $propertyPath ??= $name;
        $parsedValue = null;

        if ($isNullable && \is_string($param) && $this->urlParser->isNull($param)) {
            return null;
        }

        if (Type::BUILTIN_TYPE_ARRAY !== $type && \is_array($param)) {
            $this->urlParser->handleFailure($name, $className, $type, '[]', $propertyPath);
        } elseif (\is_string($param)) {
            if (Type::BUILTIN_TYPE_INT === $type) {
                $parsedValue = $this->urlParser->parseInteger($param);
            } elseif (Type::BUILTIN_TYPE_FLOAT === $type) {
                $parsedValue = $this->urlParser->parseFloat($param);
            } elseif (Type::BUILTIN_TYPE_BOOL === $type) {
                $parsedValue = $this->urlParser->parseBoolean($param);
            } elseif (Type::BUILTIN_TYPE_STRING === $type) {
                $parsedValue = $this->urlParser->parseString($param);
            }
        } elseif (Type::BUILTIN_TYPE_ARRAY === $type) {
            $parsedValue = $this->parseArrayProperty($className, $name, $param, $propertyPath);
        }

        if (null === $parsedValue) {
            $this->urlParser->handleFailure($name, $className, $type, \is_array($param) ? '[]' : $param, $propertyPath);
        }

        return $parsedValue;
    }

    private function getPropertyInfoExtractor(): PropertyInfoExtractor
    {
        if (null === $this->propertyInfoExtractor) {
            $this->propertyInfoExtractor = new PropertyInfoExtractor([],
                [new PhpDocExtractor(), new ReflectionExtractor()]);
        }

        return $this->propertyInfoExtractor;
    }
}
