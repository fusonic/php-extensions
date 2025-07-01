<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Application\Messenger\Bus;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\StampInterface;

interface EventBusInterface
{
    /**
     * Dispatch with the {@see DispatchAfterCurrentBusStamp} stamp.
     *
     * @param StampInterface[] $stamps
     */
    public function dispatchAfterCurrentBus(object $message, array $stamps = []): Envelope;

    /**
     * @param StampInterface[] $stamps
     */
    public function dispatch(object $message, array $stamps = []): Envelope;
}
