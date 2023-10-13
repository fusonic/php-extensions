<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Controller;

use Fusonic\HttpKernelBundle\Attribute\FromRequest;
use Fusonic\HttpKernelBundle\ErrorHandler\ConstraintViolationErrorHandler;
use Fusonic\HttpKernelBundle\ErrorHandler\ErrorHandlerInterface;
use Fusonic\HttpKernelBundle\Provider\ContextAwareProviderInterface;
use Fusonic\HttpKernelBundle\Request\RequestDataCollectorInterface;
use Fusonic\HttpKernelBundle\Request\StrictRequestDataCollector;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestDtoResolver implements ValueResolverInterface
{
    public const METHODS_WITH_STRICT_TYPE_CHECKS = [
        Request::METHOD_PUT,
        Request::METHOD_POST,
        Request::METHOD_DELETE,
        Request::METHOD_PATCH,
    ];

    private ErrorHandlerInterface $errorHandler;
    private RequestDataCollectorInterface $requestDataCollector;

    public function __construct(
        private readonly DenormalizerInterface $serializer,
        private readonly ValidatorInterface $validator,
        ?ErrorHandlerInterface $errorHandler = null,
        /**
         * @var iterable<ContextAwareProviderInterface>
         */
        #[TaggedIterator(tag: ContextAwareProviderInterface::TAG_CONTEXT_AWARE_PROVIDER)]
        private readonly iterable $providers = [],
        ?RequestDataCollectorInterface $modelDataParser = null,
    ) {
        $this->errorHandler = $errorHandler ?? new ConstraintViolationErrorHandler();
        $this->requestDataCollector = $modelDataParser ?? new StrictRequestDataCollector();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (!$this->isSupportedArgument($argument)) {
            return;
        }

        /** @var class-string $className */
        $className = $argument->getType();

        $data = [];
        try {
            $data = $this->requestDataCollector->collect($request, $className);

            $dto = $this->denormalize($data, $className);
        } catch (\Throwable $ex) {
            throw $this->errorHandler->handleDenormalizeError($ex, $data, $className);
        }

        $this->applyProviders($dto);
        $this->validate($dto);

        yield $dto;
    }

    private function applyProviders(object $dto): void
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($dto)) {
                $provider->provide($dto);
            }
        }
    }

    private function isSupportedArgument(ArgumentMetadata $argument): bool
    {
        // no type and nonexistent classes should be ignored
        if (!\is_string($argument->getType()) || '' === $argument->getType() || !class_exists($argument->getType())) {
            return false;
        }

        // attribute via parameter
        if (\count($argument->getAttributes(FromRequest::class)) > 0) {
            return true;
        }

        // attribute via class
        $class = new \ReflectionClass($argument->getType());
        $attributes = $class->getAttributes(FromRequest::class, \ReflectionAttribute::IS_INSTANCEOF);

        return \count($attributes) > 0;
    }

    /**
     * @param array<mixed> $data
     * @param class-string $class
     */
    private function denormalize(array $data, string $class): object
    {
        if (\count($data) > 0) {
            $dto = $this->serializer->denormalize($data, $class, JsonEncoder::FORMAT);
        } else {
            $dto = new $class();
        }

        return $dto;
    }

    private function validate(object $dto): void
    {
        $violations = $this->validator->validate($dto);
        if ($violations->count() > 0) {
            $this->errorHandler->handleConstraintViolations($violations);
        }
    }
}
