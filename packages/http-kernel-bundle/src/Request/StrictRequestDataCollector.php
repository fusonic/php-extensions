<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Request;

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
    private bool $strictRouteParams;
    private bool $strictQueryParams;

    /**
     * @param array<string, RequestBodyParserInterface>|null $requestBodyParsers
     */
    public function __construct(
        ?UrlParserInterface $urlParser = null,
        ?array $requestBodyParsers = null,
        bool $strictRouteParams = false,
        bool $strictQueryParams = false,
    ) {
        $this->urlParser = $urlParser ?? new FilterVarUrlParser();
        $this->requestBodyParsers = $requestBodyParsers ?? [
            'json' => new JsonRequestBodyParser(),
            'default' => new FormRequestBodyParser(),
        ];
        $this->strictRouteParams = $strictRouteParams;
        $this->strictQueryParams = $strictQueryParams;
    }

    public function collect(Request $request, string $className): array
    {
        if ($this->strictRouteParams) {
            $routeParameters = $this->parseUrlProperties($request->attributes->get('_route_params', []), $className);
        } else {
            $routeParameters = $request->attributes->get('_route_params', []);
        }

        if (\in_array($request->getMethod(), self::METHODS_WITH_STRICT_TYPE_CHECKS, true)) {
            return $this->mergeRequestData($this->parseRequestBody($request), $routeParameters);
        }

        if ($this->strictQueryParams) {
            $queryParameters = $this->parseUrlProperties($request->query->all(), $className);
        } else {
            $queryParameters = $request->query->all();
        }

        return $this->mergeRequestData($queryParameters, $routeParameters);
    }

    /**
     * @param array<mixed>         $data
     * @param array<string, mixed> $routeParameters
     *
     * @return array<string, mixed>
     */
    protected function mergeRequestData(array $data, array $routeParameters): array
    {
        if (\count($keys = array_intersect_key($data, $routeParameters)) > 0) {
            throw new BadRequestHttpException(
                sprintf(
                    'Parameters (%s) used as route attributes can not be used in the request body or query parameters.',
                    implode(', ', array_keys($keys))
                )
            );
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
        foreach ($params as $name => $param) {
            $types = $this->getPropertyInfoExtractor()->getTypes($className, $name);

            if (null !== $types) {
                if (\count($types) > 1) {
                    throw UnionTypeNotSupportedException::fromTypes($types);
                }

                $type = $types[0];
                $typeName = $type->getBuiltinType();

                if ('object' === $typeName) {
                    $typeClassName = $type->getClassName();

                    if (null !== $typeClassName && TypeHelper::isUnionType($typeClassName)) {
                        throw new UnionTypeNotSupportedException($typeClassName);
                    }

                    if (null !== $typeClassName && TypeHelper::isTypeEnum($typeClassName)) {
                        $typeName = TypeHelper::ENUM_TYPE;
                    } else {
                        throw new ObjectTypeNotSupportedException();
                    }
                }

                if (\is_string($param) || \is_array($param)) {
                    $params[$name] = $this->parseUrlProperty(
                        $className,
                        $name,
                        $typeName,
                        $type->isNullable(),
                        $param,
                        $this->appendPropertyPath($propertyPath, $name)
                    );
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
    private function parseArrayProperty(
        string $className,
        string $name,
        array $param,
        ?string $propertyPath = null
    ): array {
        $arrayPropertyTypes = $this->getPropertyInfoExtractor()->getTypes($className, $name);

        if (null === $arrayPropertyTypes) {
            return [];
        }

        $parsedValues = [];

        foreach ($arrayPropertyTypes as $arrayPropertyType) {
            $collectionValueTypes = $arrayPropertyType->getCollectionValueTypes();

            foreach ($collectionValueTypes as $collectionValueType) {
                $typeName = $collectionValueType->getBuiltinType();

                foreach ($param as $key => $arrayItem) {
                    if ('object' === $typeName) {
                        $collectionValueClassName = $collectionValueType->getClassName();

                        if (null !== $collectionValueClassName && TypeHelper::isUnionType($collectionValueClassName)) {
                            throw new UnionTypeNotSupportedException($collectionValueClassName);
                        }

                        if (null !== $collectionValueClassName && TypeHelper::isTypeEnum($collectionValueClassName)) {
                            $typeName = TypeHelper::ENUM_TYPE;
                        } else {
                            throw new ObjectTypeNotSupportedException();
                        }
                    }

                    $parsedValues[$key] = $this->parseUrlProperty(
                        $className,
                        $name,
                        $typeName,
                        $collectionValueType->isNullable(),
                        $arrayItem,
                        $this->appendPropertyPath($propertyPath, $key),
                    );
                }
            }
        }
        // Use the original value if no types can be parsed
        if (0 === \count($parsedValues)) {
            $parsedValues = $param;
        }

        return $parsedValues;
    }

    /**
     * @param class-string    $className
     * @param string|string[] $param
     */
    private function parseUrlProperty(
        string $className,
        string $name,
        string $type,
        bool $isNullable,
        string|array $param,
        ?string $propertyPath = null,
    ): mixed {
        $propertyPath ??= $name;
        $parsedValue = null;

        if ($isNullable && \is_string($param) && $this->urlParser->isNull($param)) {
            return null;
        }

        if (Type::BUILTIN_TYPE_ARRAY !== $type && \is_array($param)) {
            $this->urlParser->handleFailure($name, $className, $type, '[]', $propertyPath);
        } elseif (Type::BUILTIN_TYPE_ARRAY !== $type && \is_string($param)) {
            if (Type::BUILTIN_TYPE_INT === $type) {
                $parsedValue = $this->urlParser->parseInteger($param);
            } elseif (Type::BUILTIN_TYPE_FLOAT === $type) {
                $parsedValue = $this->urlParser->parseFloat($param);
            } elseif (Type::BUILTIN_TYPE_BOOL === $type) {
                $parsedValue = $this->urlParser->parseBoolean($param);
            } elseif (Type::BUILTIN_TYPE_STRING === $type) {
                $parsedValue = $this->urlParser->parseString($param);
            } elseif (TypeHelper::ENUM_TYPE === $type) {
                $parsedValue = $this->urlParser->parseString($param);
            }
        } elseif (Type::BUILTIN_TYPE_ARRAY === $type) {
            $arrayValues = $this->urlParser->handleArrayParameter($param);
            $parsedValue = $this->parseArrayProperty($className, $name, $arrayValues, $propertyPath);
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
