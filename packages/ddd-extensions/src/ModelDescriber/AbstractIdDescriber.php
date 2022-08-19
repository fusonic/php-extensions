<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\ModelDescriber;

use Fusonic\DDDExtensions\Domain\Model\AbstractId;
use Fusonic\DDDExtensions\Reflection\ReflectionHelper;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

/**
 * A ModelDescriber for AbstractId value objects to display them properly in the generated API docs.
 */
class AbstractIdDescriber implements ModelDescriberInterface
{
    public function supports(Model $model): bool
    {
        $type = $model->getType();
        /** @var class-string|null $className */
        $className = $type->getClassName();

        return 'object' === $type->getBuiltinType()
            && null !== $className
            && ReflectionHelper::isInstanceOf($className, AbstractId::class);
    }

    public function describe(Model $model, Schema $schema): void
    {
        $schema->example = '1';
    }
}
