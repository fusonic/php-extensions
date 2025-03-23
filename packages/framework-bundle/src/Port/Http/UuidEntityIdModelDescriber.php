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

if (class_exists(ModelDescriberInterface::class)) {
    /**
     * A ModelDescriber for {@link UuidEntityId} value objects to display them properly in the generated Nelmio API docs.
     */
    final readonly class UuidEntityIdModelDescriber implements ModelDescriberInterface
    {
        public function supports(Model $model): bool
        {
            $type = $model->getType();

            /** @var class-string|null $className */
            $className = $type->getClassName();

            return 'object' === $type->getBuiltinType()
                && null !== $className
                && is_a($className, UuidEntityId::class, true);
        }

        public function describe(Model $model, Schema $schema): void
        {
            $type = $model->getType();

            /** @var class-string|null $className */
            $className = $type->getClassName();

            if (null !== $className && is_a($className, UuidEntityId::class, true)) {
                $schema->type = 'string';
                $schema->format = 'uuid';
            }
        }
    }
}
