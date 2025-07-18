<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Port\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

trait ControllerResponseTrait
{
    protected readonly SerializerInterface $serializer;

    /**
     * @throws ExceptionInterface
     */
    public function createJsonResponse(mixed $result, int $status = Response::HTTP_OK): JsonResponse
    {
        $data = $this->serializer->serialize(
            data: $result,
            format: JsonEncoder::FORMAT,
            context: [
                JsonEncode::OPTIONS => JsonResponse::DEFAULT_ENCODING_OPTIONS |
                    \JSON_PRETTY_PRINT |
                    \JSON_UNESCAPED_UNICODE |
                    \JSON_PRESERVE_ZERO_FRACTION,
            ],
        );

        return JsonResponse::fromJsonString($data, $status);
    }
}
