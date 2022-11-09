<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Describer;

use Fusonic\ApiDocumentationBundle\AnnotationBuilder\AnnotationBuilder;
use Fusonic\ApiDocumentationBundle\Attribute\DocumentedRoute;
use Fusonic\ApiDocumentationBundle\Exception\DuplicateAttributesException;
use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\PathItem;
use OpenApi\Context;
use OpenApi\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class DocumentedRouteDescriber implements DescriberInterface
{
    use SetsContextTrait;
    use RouteDescriberTrait;

    /**
     * @var \ReflectionClass<object>|null
     */
    private ?\ReflectionClass $requestObjectReflectionClass;

    /**
     * @param class-string|null $requestObjectClass
     */
    public function __construct(
        private readonly RouteCollection $routeCollection,
        private readonly ControllerReflector $controllerReflector,
        private readonly LoggerInterface $logger,
        ?string $requestObjectClass = null,
    ) {
        if (null !== $requestObjectClass && !class_exists($requestObjectClass)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist.', $requestObjectClass));
        } elseif (null !== $requestObjectClass) {
            $this->requestObjectReflectionClass = new \ReflectionClass($requestObjectClass);
        }
    }

    public function describe(OA\OpenApi $api): void
    {
        /** @var \ReflectionMethod $method */
        foreach ($this->getMethodsToParse() as $method => [$path, $httpMethods, $routeName]) {
            $pathItem = Util::getPath($api, $path);

            $this->setContext($this->createContext($pathItem, $method));

            $documentedRoute = $this->getDocumentedRouteObject($method);

            if (null === $documentedRoute) {
                continue;
            }

            $annotationBuilder = (new AnnotationBuilder($documentedRoute, $method, $this->requestObjectReflectionClass));

            foreach ($httpMethods as $httpMethod) {
                $implicitAnnotations = array_filter([
                    $annotationBuilder->getOutputAnnotation($httpMethod),
                    $annotationBuilder->getInputAnnotation($httpMethod),
                ]);

                $operation = Util::getOperation($pathItem, $httpMethod);
                $operation->merge($implicitAnnotations);

                if (Generator::UNDEFINED === $operation->operationId) {
                    $operation->operationId = $httpMethod.'_'.$routeName;
                }
            }
        }

        // Reset the Generator after the parsing
        $this->setContext(null);
    }

    private function getMethodsToParse(): \Generator
    {
        foreach ($this->routeCollection->all() as $routeName => $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }
            $controller = $route->getDefault('_controller');
            $reflectedMethod = $this->controllerReflector->getReflectionMethod($controller);
            if (null === $reflectedMethod) {
                continue;
            }
            $path = $this->normalizePath($route->getPath());
            $supportedHttpMethods = $this->getSupportedHttpMethods($route);
            if (0 === count($supportedHttpMethods)) {
                $this->logger->warning(
                    'None of the HTTP methods specified for path {path} are supported by swagger-ui, skipping this path',
                    [
                        'path' => $path,
                    ]
                );

                continue;
            }
            yield $reflectedMethod => [$path, $supportedHttpMethods, $routeName];
        }
    }

    private function getSupportedHttpMethods(Route $route): array
    {
        $allMethods = Util::OPERATIONS;
        $methods = array_map('strtolower', $route->getMethods());

        return array_intersect(count($methods) > 0 ? $methods : $allMethods, $allMethods);
    }

    private function createContext(PathItem $path, \ReflectionMethod $method): Context
    {
        $context = Util::createContext(['nested' => $path], $path->_context);
        $context->namespace = $method->getNamespaceName();
        $context->class = $method->getDeclaringClass()->getShortName();
        $context->method = $method->name;
        $context->filename = (string) $method->getFileName();

        return $context;
    }

    private function getDocumentedRouteObject(\ReflectionMethod $method): ?DocumentedRoute
    {
        $attributes = $method->getAttributes(DocumentedRoute::class);

        if (0 === count($attributes)) {
            return null;
        }

        if (count($attributes) > 1) {
            throw new DuplicateAttributesException(DocumentedRoute::class);
        }

        return $attributes[0]->newInstance();
    }
}
