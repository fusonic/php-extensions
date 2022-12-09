<?php

namespace Fusonic\HttpKernelExtensions\Tests\Controller;

use Fusonic\HttpKernelExtensions\Attribute\FromRequest;
use Fusonic\HttpKernelExtensions\Controller\RequestDtoResolver;
use Fusonic\HttpKernelExtensions\Tests\Dto\NestedDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParameterObjectTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testQueryParameterObjectHandling(): void
    {
        $query = [
            'objectArgument' => [
                'requiredArgument' => '10',
            ],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(NestedDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $dto = $generator->current();
        self::assertInstanceOf(NestedDto::class, $dto);

        $nestedObject = $dto->getObjectArgument();
        self::assertSame(10, $nestedObject->getRequiredArgument());
    }
}
