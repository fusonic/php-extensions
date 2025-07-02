<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\SentryCron\Tests;

use Fusonic\SentryCron\SentrySchedulerEventSubscriber;
use PHPUnit\Framework\TestCase;

final class SentrySchedulerEventSubscriberTest extends TestCase
{
    /**
     * This doesn't actually test anything.
     */
    public function testConstructor(): void
    {
        $sentrySchedulerEventSubscriber = new SentrySchedulerEventSubscriber(true, 10);

        /* @phpstan-ignore-next-line */
        self::assertTrue(method_exists($sentrySchedulerEventSubscriber, 'onFailure'));
        /* @phpstan-ignore-next-line */
        self::assertTrue(method_exists($sentrySchedulerEventSubscriber, 'onPreRun'));
        /* @phpstan-ignore-next-line */
        self::assertTrue(method_exists($sentrySchedulerEventSubscriber, 'onPostRun'));

        $sentrySchedulerEventSubscriber = new SentrySchedulerEventSubscriber(false);

        /* @phpstan-ignore-next-line */
        self::assertNotNull($sentrySchedulerEventSubscriber);
    }
}
