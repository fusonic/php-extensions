<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\Resolver;

use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\UidValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The code of this class has been copied over from {@see UidValueResolver} and was adjusted to fit the needs of the
 * custom {@see UuidEntityId} implementation.
 */
final readonly class UuidEntityIdValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<UuidEntityId>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->isVariadic()
            || !\is_string($value = $request->attributes->get($argument->getName()))
            || null === ($uuidEntityIdClass = $argument->getType())
            || !is_subclass_of($uuidEntityIdClass, UuidEntityId::class, true)
        ) {
            return [];
        }

        try {
            return [$uuidEntityIdClass::fromString($value)];
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(\sprintf('The UUID for the "%s" parameter is invalid.', $argument->getName()), $e);
        }
    }
}
