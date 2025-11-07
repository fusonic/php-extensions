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

If you have an unpredictable longer-running scheduled task, you can manually check in by implementing `AsyncCheckInScheduleEventInterface`.

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

The manual check in:

```php

class SomeEventHandler {
    private const BATCH_SIZE = 100;
    
    public function __invoke(SomeEvent $event): void {
        $offset = 0;
        
        // e.g.: some slow database processing
        $entitiesToProcess = // ...
        
        $nextEvent = new SomeEvent(offset: $offset + self::BATCH_SIZE);
        
        if (count($entitiesToProcess) === 0) {
            $nextEvent->markAsLast();
        }
        
        $this->eventBus->dispatch($nextEvent);
    }

}
```

