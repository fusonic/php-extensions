<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Controller;

use Fusonic\HttpKernelBundle\Attribute\FromRequest;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Tests\Dto\EnumDto;
use Fusonic\HttpKernelBundle\Tests\Dto\ExampleStringBackedEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class BodyEnumTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testBodyWithValidEnumValue(): void
    {
        /** @var string $data */
        $data = json_encode(
            [
                'exampleEnum' => ExampleStringBackedEnum::CHOICE_1->value,
            ]
        );

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $iterable = $resolver->resolve($request, $argument);

        $dto = $iterable->current();
        self::assertInstanceOf(EnumDto::class, $dto);
        self::assertSame(ExampleStringBackedEnum::CHOICE_1, $dto->exampleEnum);
    }

    public function testBodyWithInvalidEnumValue(): void
    {
        /** @var string $data */
        $data = json_encode(
            [
                'exampleEnum' => 'WRONG_CHOICE',
            ]
        );

        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $request->setMethod(Request::METHOD_POST);
        $argument = $this->createArgumentMetadata(EnumDto::class, [new FromRequest()]);

        $resolver = $this->getRequestDtoResolver();
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('exampleEnum', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame(
            'The value you selected is not a valid choice.',
            $constraintViolationList->get(0)->getMessage()
        );
        self::assertSame('WRONG_CHOICE', $constraintViolationList->get(0)->getInvalidValue());
    }
}
