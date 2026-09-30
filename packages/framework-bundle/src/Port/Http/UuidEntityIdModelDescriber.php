<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Port\Http;

use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use OpenApi\Annotations\Schema;

/**
 * A ModelDescriber for {@link UuidEntityId} value objects to display them properly in the generated Nelmio API docs.
 */
final readonly class UuidEntityIdModelDescriber implements ModelDescriberInterface
{
    public function supports(Model $model): bool
    {
        return $model->getTypeInfo()->isIdentifiedBy(UuidEntityId::class);
    }

    public function describe(Model $model, Schema $schema): void
    {
        if ($model->getTypeInfo()->isIdentifiedBy(UuidEntityId::class)) {
            $schema->type = 'string';
            $schema->format = 'uuid';
        }
    }
}
