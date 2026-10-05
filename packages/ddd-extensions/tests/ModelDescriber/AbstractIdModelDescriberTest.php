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
use Symfony\Component\TypeInfo\Type\ObjectType;

final class AbstractIdModelDescriberTest extends AbstractTestCase
{
    public function testSupports(): void
    {
        if (!class_exists(ObjectType::class)) {
            self::markTestSkipped('Skipped due to missing "symfony/type-info" which is not available in Symfony 6.4');
        }

        $unsupportedModel = new Model(
            new ObjectType(AddressValueObject::class),
        );
        $supportedModel = new Model(
            new ObjectType(JobId::class),
        );

        $describer = new EntityIdDescriber();

        self::assertFalse($describer->supports($unsupportedModel));
        self::assertTrue($describer->supports($supportedModel));
    }

    public function testDescribe(): void
    {
        if (!class_exists(ObjectType::class)) {
            self::markTestSkipped('Skipped due to missing "symfony/type-info" which is not available in Symfony 6.4');
        }

        $model = new Model(
            new ObjectType(JobId::class),
        );

        $describer = new EntityIdDescriber();
        $schema = new Schema([]);

        $describer->describe($model, $schema);

        self::assertSame('integer', $schema->type);
    }
}
