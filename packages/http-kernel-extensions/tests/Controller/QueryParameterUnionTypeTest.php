<?php

namespace Fusonic\HttpKernelExtensions\Tests\Controller;

use Fusonic\HttpKernelExtensions\Attribute\FromRequest;
use Fusonic\HttpKernelExtensions\Controller\RequestDtoResolver;
use Fusonic\HttpKernelExtensions\Tests\Dto\UnionTypeDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryParameterUnionTypeTest extends TestCase
{
    use RequestDtoResolverTestTrait;

    public function testQueryParameterArrayUnionType(): void
    {
        $query = [
            'unionTypes' => [
                ['id' => 1],
                ['test' => 'test'],
            ],
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(UnionTypeDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $this->expectExceptionMessage('Using union types in the url is not supported. Type: (\Fusonic\HttpKernelExtensions\Tests\Dto\StringIdDto|\Fusonic\HttpKernelExtensions\Tests\Dto\SubTypeDto)');

        $generator->current();
    }

    public function testQueryParameterUnionType(): void
    {
        $query = [
            'unionType' => false,
        ];
        $request = new Request($query);
        $request->setMethod(Request::METHOD_GET);
        $argument = $this->createArgumentMetadata(UnionTypeDto::class, [new FromRequest()]);

        $resolver = new RequestDtoResolver($this->getDenormalizer(), $this->getValidator());
        $generator = $resolver->resolve($request, $argument);

        $this->expectExceptionMessage('Using union types in the url is not supported. Type: (Fusonic\HttpKernelExtensions\Tests\Dto\StringIdDto|int)');

        $generator->current();
    }
}
