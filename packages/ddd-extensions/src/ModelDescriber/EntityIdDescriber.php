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
        return $model->getTypeInfo()->isIdentifiedBy(EntityId::class);
    }

    public function describe(Model $model, Schema $schema): void
    {
        if ($model->getTypeInfo()->isIdentifiedBy(EntityIntegerId::class)) {
            $schema->type = 'integer';
        }
    }
}
