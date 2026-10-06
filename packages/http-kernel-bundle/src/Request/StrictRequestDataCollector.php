<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Request;

use Fusonic\HttpKernelBundle\Exception\UnionTypeNotSupportedException;
use Fusonic\HttpKernelBundle\Request\BodyParser\RequestBodyParserInterface;
use Fusonic\HttpKernelBundle\Request\UrlParser\FilterVarUrlParser;
use Fusonic\HttpKernelBundle\Request\UrlParser\UrlParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\EnumType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\Type\UnionType;
use Symfony\Component\TypeInfo\TypeIdentifier;

readonly class StrictRequestDataCollector extends RequestDataCollector
{
    private const array SCALAR_TYPES = [
        TypeIdentifier::INT,
        TypeIdentifier::FLOAT,
        TypeIdentifier::BOOL,
        TypeIdentifier::STRING,
    ];

    private UrlParserInterface $urlParser;
    private PropertyInfoExtractor $propertyInfoExtractor;

    /**
     * @param array<string, RequestBodyParserInterface>|null $requestBodyParsers
     */
    public function __construct(
        ?UrlParserInterface $urlParser = null,
        ?array $requestBodyParsers = null,
    ) {
        parent::__construct(forceRouteParamsIntegers: false, requestBodyParsers: $requestBodyParsers);

        $this->urlParser = $urlParser ?? new FilterVarUrlParser();
        $this->propertyInfoExtractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
    }

    #[\Override]
    public function collect(Request $request, string $className): array
    {
        $routeParameters = $this->parseUrlProperties($request->attributes->get('_route_params', []), $className);

        if (\in_array($request->getMethod(), self::METHODS_WITH_REQUEST_BODY, true)) {
            $body = $this->parseRequestBody($request);

            if ('json' !== $request->getContentTypeFormat()) {
                $body = $this->parseUrlProperties($body, $className);
            }

            return $this->mergeRequestData($body, $routeParameters);
        }

        $queryParameters = $this->parseUrlProperties($request->query->all(), $className);

        return $this->mergeRequestData($queryParameters, $routeParameters);
    }

    #[\Override]
    public function getDenormalizationContext(Request $request): array
    {
        return [];
    }

    /**
     * Route parameters, query parameters and form request bodies always come in as strings, so they are parsed into the
     * types declared in the class.
     * Invalid values are passed to the url parser's failure handling.
     *
     * @param class-string            $className
     * @param array<array-key, mixed> $params
     *
     * @return array<array-key, mixed>
     */
    private function parseUrlProperties(array $params, string $className, ?string $parentPropertyPath = null): array
    {
        foreach ($params as $name => $param) {
            $name = (string) $name;
            $type = $this->propertyInfoExtractor->getType($className, $name);

            if (null !== $type) {
                $propertyPath = null === $parentPropertyPath ? $name : $this->appendPropertyPath($parentPropertyPath, $name);
                $params[$name] = $this->parseUrlValue($className, $name, $type, $param, $propertyPath);
            }
        }

        return $params;
    }

    /**
     * @param class-string $className
     */
    private function parseUrlValue(string $className, string $name, Type $type, mixed $value, string $propertyPath): mixed
    {
        if (!\is_string($value) && !\is_array($value)) {
            return $value;
        }

        if ($type->isNullable() && \is_string($value) && $this->urlParser->isNull($value)) {
            return null;
        }

        if ($type instanceof NullableType) {
            $type = $type->getWrappedType();
        }

        if ($this->isArrayType($type)) {
            return $this->parseArrayValue($className, $name, $type, $value, $propertyPath);
        }

        if ($type instanceof ObjectType && null === $this->getScalarTypeName($type)) {
            return $this->parseObjectValue($className, $name, $type, $value, $propertyPath);
        }

        $scalarTypes = $type instanceof UnionType ? $type->getTypes() : [$type];
        $typeNames = array_map($this->getScalarTypeName(...), $scalarTypes);

        if (\in_array(null, $typeNames, true)) {
            if ($type instanceof UnionType) {
                throw new UnionTypeNotSupportedException((string) $type);
            }

            return $value;
        }

        $expectedType = implode('|', $typeNames);

        if (\is_array($value)) {
            $this->urlParser->handleFailure($name, $className, $expectedType, '[]', $propertyPath);

            return $value;
        }

        foreach ($this->sortBySpecificity($typeNames) as $typeName) {
            $parsedValue = $this->parseScalarValue($typeName, $value);

            if (null !== $parsedValue) {
                return $parsedValue;
            }
        }

        $this->urlParser->handleFailure($name, $className, $expectedType, $value, $propertyPath);

        return $value;
    }

    /**
     * @param class-string        $className
     * @param string|array<mixed> $value
     *
     * @return array<array-key, mixed>
     */
    private function parseArrayValue(string $className, string $name, Type $type, string|array $value, string $propertyPath): array
    {
        $values = $this->urlParser->handleArrayParameter($value);
        $valueType = $type instanceof CollectionType ? $type->getCollectionValueType() : null;

        if (null === $valueType || $valueType->isIdentifiedBy(TypeIdentifier::MIXED)) {
            return $values;
        }

        $parsedValues = [];

        foreach ($values as $key => $item) {
            $parsedValues[$key] = $this->parseUrlValue($className, $name, $valueType, $item, $this->appendPropertyPath($propertyPath, $key));
        }

        return $parsedValues;
    }

    /**
     * @param class-string             $className
     * @param ObjectType<class-string> $type
     * @param string|array<mixed>      $value
     */
    private function parseObjectValue(string $className, string $name, ObjectType $type, string|array $value, string $propertyPath): mixed
    {
        /** @var class-string $objectClassName */
        $objectClassName = $type->getClassName();

        if (\is_array($value)) {
            return $this->parseUrlProperties($value, $objectClassName, $propertyPath);
        }

        $this->urlParser->handleFailure($name, $className, $objectClassName, $value, $propertyPath);

        return $value;
    }

    private function isArrayType(Type $type): bool
    {
        return $type instanceof CollectionType
            || ($type instanceof BuiltinType && TypeIdentifier::ARRAY === $type->getTypeIdentifier());
    }

    /**
     * Returns null for types that cannot be represented as a single url value, like objects, arrays or mixed.
     */
    private function getScalarTypeName(Type $type): ?string
    {
        if ($this->isDenormalizedFromString($type)) {
            return TypeIdentifier::STRING->value;
        }

        if ($type instanceof BuiltinType && \in_array($type->getTypeIdentifier(), self::SCALAR_TYPES, true)) {
            return $type->getTypeIdentifier()->value;
        }

        return null;
    }

    /**
     * Enums and dates are passed to the serializer as strings.
     */
    private function isDenormalizedFromString(Type $type): bool
    {
        if ($type instanceof EnumType) {
            return true;
        }

        return $type instanceof ObjectType
            && (enum_exists($type->getClassName()) || is_a($type->getClassName(), \DateTimeInterface::class, true));
    }

    /**
     * Every string is a valid string, so the most restrictive types are tried first. For example "1" for int|string
     * will be parsed as an integer.
     *
     * @param list<string> $typeNames
     *
     * @return list<string>
     */
    private function sortBySpecificity(array $typeNames): array
    {
        $order = [TypeIdentifier::INT->value, TypeIdentifier::FLOAT->value, TypeIdentifier::BOOL->value, TypeIdentifier::STRING->value];
        usort($typeNames, static fn (string $a, string $b): int => array_search($a, $order, true) <=> array_search($b, $order, true));

        return $typeNames;
    }

    private function parseScalarValue(string $typeName, string $value): int|float|bool|string|null
    {
        return match ($typeName) {
            TypeIdentifier::INT->value => $this->urlParser->parseInteger($value),
            TypeIdentifier::FLOAT->value => $this->urlParser->parseFloat($value),
            TypeIdentifier::BOOL->value => $this->urlParser->parseBoolean($value),
            default => $this->urlParser->parseString($value),
        };
    }

    private function appendPropertyPath(string $propertyPath, string|int $key): string
    {
        if (\is_string($key)) {
            return \sprintf('%s.%s', $propertyPath, $key);
        }

        return \sprintf('%s[%s]', $propertyPath, $key);
    }
}
