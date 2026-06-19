<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Describer;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Routing\Route;

final readonly class DocumentedErrorRouteDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function __construct(
        private DocumentedErrorDescriber $documentedErrorDescriber,
    ) {
    }

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        $methods = $route->getMethods();

        if ([] === $methods) {
            $methods = Util::OPERATIONS;
        }

        $path = Util::getPath($api, $this->normalizePath($route->getPath()));
        $context = Util::createContext(['nested' => $path], $path->_context);
        $context->namespace = $reflectionMethod->getNamespaceName();
        $context->class = $reflectionMethod->getDeclaringClass()->getShortName();
        $context->method = $reflectionMethod->name;
        $context->filename = (string) $reflectionMethod->getFileName();

        Generator::$context = $context;

        foreach ($methods as $method) {
            $method = strtolower($method);

            if (!\in_array($method, Util::OPERATIONS, true)) {
                continue;
            }

            $responses = $this->documentedErrorDescriber->buildResponseAnnotations($reflectionMethod, $method);

            if ([] === $responses) {
                continue;
            }

            $operation = Util::getOperation($path, $method);
            $operation->merge($responses);
        }

        Generator::$context = null;
    }
}
