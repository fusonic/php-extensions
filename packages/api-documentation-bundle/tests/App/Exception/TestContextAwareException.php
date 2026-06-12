<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App\Exception;

use Fusonic\ApiDocumentationBundle\Tests\App\ErrorContext\TestErrorContextData;

final class TestContextAwareException extends \RuntimeException implements ContextAwareExceptionInterface
{
    public function __construct(private readonly TestErrorContextData $context)
    {
        parent::__construct();
    }

    public function getContext(): mixed
    {
        return $this->context;
    }
}
