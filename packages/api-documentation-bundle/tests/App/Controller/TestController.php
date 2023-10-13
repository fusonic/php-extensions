<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App\Controller;

use Fusonic\ApiDocumentationBundle\Attribute\DocumentedRoute;
use Fusonic\ApiDocumentationBundle\Tests\App\FromRequest;
use Fusonic\ApiDocumentationBundle\Tests\App\Request\TestRequest;
use Fusonic\ApiDocumentationBundle\Tests\App\Response\TestResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class TestController extends AbstractController
{
    #[DocumentedRoute(
        path: '/test-manual-output/{id}',
        methods: ['GET'],
        output: TestResponse::class,
    )]
    public function testManualOutput(#[FromRequest] TestRequest $query): Response
    {
        return new Response((string) $query->id, 200);
    }

    #[DocumentedRoute(path: '/test-status-code/{id}', methods: ['GET'], statusCode: 422)]
    public function testStatusCode(#[FromRequest] TestRequest $query): Response
    {
        return new Response((string) $query->id, 422);
    }

    #[DocumentedRoute(path: '/test-return-type/{id}', methods: ['GET'])]
    public function testReturnType(#[FromRequest] TestRequest $query): TestResponse
    {
        return new TestResponse($query->id);
    }

    #[DocumentedRoute(path: '/test-builtin-return-type/{id}', methods: ['GET'])]
    public function testBuiltinReturnType(#[FromRequest] TestRequest $query): string
    {
        return (string) $query->id;
    }

    #[DocumentedRoute(path: '/test-ignored-return-type', methods: ['GET'])]
    public function testIgnoredReturnType(): Response
    {
        return new Response();
    }

    #[DocumentedRoute(path: '/annotation-builtin-type-array/{id}', methods: ['GET'])]
    /**
     * @return string[]
     */
    public function testAnnotationBuiltinTypeArray(#[FromRequest] TestRequest $query): array
    {
        return [(string) $query->id];
    }

    #[DocumentedRoute(path: '/test-annotation-custom-return-type/{id}', methods: ['GET'])]
    /**
     * @return TestResponse[]
     */
    public function testAnnotationCustomReturnTypeArray(#[FromRequest] TestRequest $query): array
    {
        return [new TestResponse($query->id)];
    }

    #[DocumentedRoute(path: '/test-combined-attributes/{id}', methods: ['POST'], description: 'Object found')]
    #[OA\Response(response: 404, description: 'Object was not found.')]
    public function testCombinedAttributes(#[FromRequest] TestRequest $query): TestResponse
    {
        return new TestResponse($query->id);
    }

    #[DocumentedRoute(path: '/test-void-return-type', methods: ['GET'])]
    public function testVoidReturnType(#[FromRequest] TestRequest $query): void
    {
    }
}
