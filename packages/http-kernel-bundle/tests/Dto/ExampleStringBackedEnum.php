<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Dto;

enum ExampleStringBackedEnum: string
{
    case CHOICE_1 = 'CHOICE_1';
    case CHOICE_2 = 'CHOICE_2';
}
