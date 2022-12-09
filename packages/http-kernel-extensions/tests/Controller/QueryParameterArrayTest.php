<?php

namespace Fusonic\HttpKernelExtensions\Tests\Controller;

use Fusonic\HttpKernelExtensions\Attribute\FromRequest;
use Fusonic\HttpKernelExtensions\Controller\RequestDtoResolver;
use Fusonic\HttpKernelExtensions\Exception\ConstraintViolationException;
use Fusonic\HttpKernelExtensions\Tests\Dto\IntArrayDto;
use Fusonic\HttpKernelExtensions\Tests\Dto\NestedDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParameterArrayTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testQueryParameterArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', '3'],
            'nullableItems' => null,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(IntArrayDto::class, $dto);
        self::assertSame([1, 2, 3], $dto->getItems());
    }

    public function testQueryParameterInvalidArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', 'true'],
            'nullableItems' => null,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('items[2]', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('true', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterInvalidNestedArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', 'nested' => 'true'],
            'nullableItems' => null,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('items.nested', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('true', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterInvalidDeepNestedArrayHandling(): void
    {
        $query = [
            'items' => ['1', '2', 'nested' => ['deep' => '1']],
            'nullableItems' => null,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(IntArrayDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('items.nested', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type int.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('[]', $constraintViolationList->get(0)->getInvalidValue());
    }

    public function testQueryParameterDeepNestedObjectArrayHandling(): void
    {
        $query = [
            'objectArgument' => [
                'requiredArgument' => 1,
            ],
            'nestedItems' => [
                ['objectArgument' => '1'],
            ],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(NestedDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $ex = null;
        try {
            $generator->current();
        } catch (ConstraintViolationException $ex) {
        }

        self::assertNotNull($ex);

        $constraintViolationList = $ex->getConstraintViolationList();
        self::assertCount(1, $constraintViolationList);

        self::assertSame('nestedItems[0].id', $constraintViolationList->get(0)->getPropertyPath());
        self::assertSame('This value should be of type string.', $constraintViolationList->get(0)->getMessage());
        self::assertSame('int', $constraintViolationList->get(0)->getInvalidValue());
    }
}
