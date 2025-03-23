<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Application\Messenger\Bus;

use Symfony\Component\Messenger\Stamp\StampInterface;

interface QueryBusInterface
{
    /**
     * @param StampInterface[] $stamps
     */
    public function dispatchAndGetResult(object $message, array $stamps = []): mixed;
}
