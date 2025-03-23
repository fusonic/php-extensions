<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Port\Http;

use Fusonic\FrameworkBundle\Application\Messenger\Bus\CommandBusInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\QueryBusInterface;

trait CommandAndQueryTrait
{
    protected readonly CommandBusInterface $commandBus;
    protected readonly QueryBusInterface $queryBus;

    protected function command(object $command): mixed
    {
        return $this->commandBus->dispatchAndGetResult($command);
    }

    protected function query(object $query): mixed
    {
        return $this->queryBus->dispatchAndGetResult($query);
    }
}
