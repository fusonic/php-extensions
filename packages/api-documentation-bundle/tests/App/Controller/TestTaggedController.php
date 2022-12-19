<?php

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App\Controller;

use Fusonic\ApiDocumentationBundle\Attribute\DocumentedRoute;
use Fusonic\ApiDocumentationBundle\Tests\App\FromRequest;
use Fusonic\ApiDocumentationBundle\Tests\App\Request\TestRequest;
use Fusonic\ApiDocumentationBundle\Tests\App\Response\TestResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

#[OA\Tag(name: 'test')]
final class TestTaggedController extends AbstractController
{
    #[DocumentedRoute(path: '/test-post-route-with-tag/{id}', methods: ['POST'])]
    public function testPostRouteWithTag(#[FromRequest] TestRequest $body): TestResponse
    {
        return new TestResponse($body->id);
    }

    #[DocumentedRoute(
        path: '/test-manual-collection-output/{id}',
        methods: ['GET'],
        input: TestRequest::class,
        output: TestResponse::class,
        outputIsCollection: true
    )]
    public function testManualCollectionOutput(TestRequest $query): JsonResponse
    {
        return new JsonResponse([(string) $query->id], 200);
    }

    #[DocumentedRoute(path: '/test-input-with-interface/{id}', methods: ['POST'])]
    public function testInputWithInterface(TestRequest $body): TestResponse
    {
        return new TestResponse($body->id);
    }
}
