<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App\ErrorContext;

class TestErrorContext
{
    public function getContext(): TestErrorContextData
    {
        return new TestErrorContextData('A solution', 'Some Problem');
    }
}
