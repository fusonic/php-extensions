# sentry-cron

[![License](https://img.shields.io/packagist/l/fusonic/sentry-cron?color=blue)](https://github.com/fusonic/php-sentry-cron/blob/master/LICENSE)
[![Latest Version](https://img.shields.io/github/tag/fusonic/php-sentry-cron.svg?color=blue)](https://github.com/fusonic/php-sentry-cron/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/fusonic/sentry-cron.svg?color=blue)](https://packagist.org/packages/fusonic/sentry-cron)
[![php 8.2+](https://img.shields.io/badge/php-min%208.2-blue.svg)](https://github.com/fusonic/php-sentry-cron/blob/master/composer.json)

* [About](#about)
* [Install](#install)
* [Usage](#usage)

## About

Automatically register scheduled events from Symfony Scheduler in Sentry Cron. Only cron expressions are supported.

## Install

Use composer to install the library from packagist.

```bash
composer require fusonic/sentry-cron
```

## Configuration

```yaml
Fusonic\SentryCron\SentrySchedulerEventSubscriber:
    arguments:
        $enabled: true
```

If you use [async events](#async-events), also register the messenger subscriber (see that
section for why it's needed in addition to the one above):

```yaml
Fusonic\SentryCron\SentryAsyncCheckInMessengerSubscriber:
    arguments:
        $enabled: true
```

## Usage
Any regular event that is triggered with a cron expression can be used.

### Event Configuration

By default, the Sentry defaults are used for monitor configurations. Per event, you can configure
an attribute to use your own configuration:

```php

use Fusonic\SentryCron\SentryMonitorConfig;

#[SentryMonitorConfig(checkinMargin: 30, maxRuntime: 30, failureIssueThreshold: 5, recoveryThreshold: 5)]
class SomeEvent {
    // ...
}
```

### Async Events

If you have an unpredictable longer-running scheduled task that batches its work and
re-dispatches itself to continue, implement `AsyncCheckInScheduleEventInterface`.

The scheduled event:

```php

use Fusonic\SentryCron\SentryMonitorConfig;
use Fusonic\SentryCron\AsyncCheckInScheduleEventInterface;
use \Fusonic\SentryCron\AsyncCheckInScheduleEventTrait;

class SomeEvent implements AsyncCheckInScheduleEventInterface {
    use AsyncCheckInScheduleEventTrait;
    
    // ...
}
```

The handler, threading the check-in ID onto each follow-up batch and marking the last one:

```php

class SomeEventHandler {
    private const BATCH_SIZE = 100;
    
    public function __invoke(SomeEvent $event): void {
        $offset = 0;
        
        // e.g.: some slow database processing
        $entitiesToProcess = // ...
        
        $nextEvent = new SomeEvent(offset: $offset + self::BATCH_SIZE);
        $nextEvent->setCheckInId($event->getCheckInId());
        
        if (count($entitiesToProcess) === 0) {
            $nextEvent->markAsLast();
        }
        
        $this->eventBus->dispatch($nextEvent);
    }

}
```

**Why this needs a second subscriber.** Symfony Scheduler's own
`PreRunEvent`/`PostRunEvent`/`FailureEvent` only fire for the message Scheduler itself
dispatched — once your handler re-dispatches a follow-up message onto a Messenger transport,
Scheduler never sees it again. `SentrySchedulerEventSubscriber` therefore only *starts* the
check-in for async events (on `PreRunEvent`); completing or failing it is handled entirely by
`SentryAsyncCheckInMessengerSubscriber`, which listens to Messenger's own
`WorkerMessageHandledEvent`/`WorkerMessageFailedEvent` instead — these fire for every batch,
on every transport hop, and are retry-aware (a failure Messenger will retry is not reported as
an error).

Those Worker events are only dispatched for messages actually consumed by a real (queued)
transport. If an async event is ever routed to `sync://`, or to a bus with no matching
transport at all, it's handled in-process without ever going through a Worker, so neither
subscriber can complete or fail its check-in — it will hang at `in_progress` until Sentry's
`maxRuntime` times it out and flags it as missed. Make sure every message implementing
`AsyncCheckInScheduleEventInterface` is routed to a transport with a real consumer.

