<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Provider;

interface ContextAwareProviderInterface
{
    final public const TAG_CONTEXT_AWARE_PROVIDER = 'fusonic.http_kernel_bundle.context_aware_provider';

    public function supports(object $dto): bool;

    public function provide(object $dto): void;
}
