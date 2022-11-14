<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Model;

/**
 * Any classes in the domain that are not an aggregate, but are an entity, should implement
 * this interface.
 */
interface EntityInterface
{
    public function getId(): mixed;
}
