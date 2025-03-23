<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Application\Messenger\Bus;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class QueryBus implements QueryBusInterface
{
    public function __construct(
        #[Autowire(service: 'query.bus')]
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function dispatchAndGetResult(object $message, array $stamps = []): mixed
    {
        return $this->bus->dispatch($message, $stamps)->last(HandledStamp::class)?->getResult();
    }
}
