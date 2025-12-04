<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\ModelDescriber;

use Fusonic\DDDExtensions\Domain\Model\EntityId;
use Fusonic\DDDExtensions\Domain\Model\EntityIntegerId;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

/**
 * A ModelDescriber for {@link EntityId} value objects to display them properly in the generated API docs.
 */
class EntityIdDescriber implements ModelDescriberInterface
{
    public function supports(Model $model): bool
    {
        // BC layer for nelmio/api-doc-bundle < 5.8
        if (!method_exists($model, 'getTypeInfo')) { // @phpstan-ignore function.alreadyNarrowedType (BC layer)
            $type = $model->getType(); // @phpstan-ignore method.deprecated (BC layer)

            /** @var class-string|null $className */
            $className = $type->getClassName(); // @phpstan-ignore method.deprecatedClass (BC layer)

            return 'object' === $type->getBuiltinType() // @phpstan-ignore method.deprecatedClass (BC layer)
                && null !== $className
                && is_a($className, EntityId::class, true);
        }

        return $model->getTypeInfo()->isIdentifiedBy(EntityId::class);
    }

    public function describe(Model $model, Schema $schema): void
    {
        // BC layer for nelmio/api-doc-bundle < 5.8
        if (!method_exists($model, 'getTypeInfo')) { // @phpstan-ignore function.alreadyNarrowedType (BC layer)
            $type = $model->getType(); // @phpstan-ignore method.deprecated (BC layer)

            /** @var class-string|null $className */
            $className = $type->getClassName();  // @phpstan-ignore method.deprecatedClass (BC layer)

            if (null !== $className && is_a($className, EntityIntegerId::class, true)) {
                $schema->type = 'integer';
            }

            return;
        }

        if ($model->getTypeInfo()->isIdentifiedBy(EntityIntegerId::class)) {
            $schema->type = 'integer';
        }
    }
}
