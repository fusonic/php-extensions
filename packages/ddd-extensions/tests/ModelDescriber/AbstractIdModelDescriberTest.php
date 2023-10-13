<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\ModelDescriber;

use Fusonic\DDDExtensions\ModelDescriber\EntityIdDescriber;
use Fusonic\DDDExtensions\Tests\AbstractTestCase;
use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;
use Fusonic\DDDExtensions\Tests\Domain\JobId;
use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;

final class AbstractIdModelDescriberTest extends AbstractTestCase
{
    public function testSupports(): void
    {
        $unsupportedModel = new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, false, AddressValueObject::class)
        );
        $supportedModel = new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, false, JobId::class)
        );

        $describer = new EntityIdDescriber();

        self::assertFalse($describer->supports($unsupportedModel));
        self::assertTrue($describer->supports($supportedModel));
    }

    public function testDescribe(): void
    {
        $model = new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, false, JobId::class)
        );

        $describer = new EntityIdDescriber();
        $schema = new Schema([]);

        $describer->describe($model, $schema);

        self::assertSame('integer', $schema->type);
    }
}
