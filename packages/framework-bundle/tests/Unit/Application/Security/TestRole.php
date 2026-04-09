<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Tests\Unit\Application\Security;

enum TestRole: string
{
    case Admin = 'ROLE_ADMIN';
}
