<?php

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
    #[DocumentedRoute(path: '/test-manual-output/{id}', methods: ['GET'], output: TestResponse::class)]
    public function testManualOutput(#[FromRequest] TestRequest $query): Response
    {
        return new Response((string) $query->id, 200);
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

    #[DocumentedRoute(path: '/test-combined-attributes/{id}', methods: ['POST'])]
    #[OA\Response(response: 404, description: 'Object was not found.')]
    public function testCombinedAttributes(#[FromRequest] TestRequest $query): TestResponse
    {
        return new TestResponse($query->id);
    }
}
