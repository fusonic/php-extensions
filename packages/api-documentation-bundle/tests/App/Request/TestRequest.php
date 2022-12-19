<?php

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App\Request;

final class TestRequest
{
    public function __construct(public int $id)
    {
    }
}
