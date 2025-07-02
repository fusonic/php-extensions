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
        $checkinMarginInMinutes: 10
```
