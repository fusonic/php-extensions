# framework-bundle

[![License](https://img.shields.io/packagist/l/fusonic/framework-bundle?color=blue)](https://github.com/fusonic/php-framework-bundle/blob/master/LICENSE)
[![Latest Version](https://img.shields.io/github/tag/fusonic/php-framework-bundle.svg?color=blue)](https://github.com/fusonic/php-framework-bundle/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/fusonic/framework-bundle.svg?color=blue)](https://packagist.org/packages/fusonic/framework-bundle)
![php 8.2+](https://img.shields.io/badge/php-%5E8.2-blue.svg)

* [About](#about)
* [Install](#install)
* [Usage](#usage)
* [Contributing](#contributing)

## About

This Symfony bundle provides an opinionated collection of classes and functionalities used throughout Fusonic's Symfony
projects, designed to simplify and standardize common development patterns.

Key features include:
- **Doctrine Entity Identifier Management**: Integration of [`symfony/uid`](https://github.com/symfony/uid) alongside
  [`fusonic/ddd-extensions`](https://github.com/fusonic/php-ddd-extensions) to allow Doctrine entities to use UUIDs in
  the form of typed classes as primary identifiers.
- **Message Bus Configurations**: Simplified handling of [`symfony/messenger`](https://github.com/symfony/messenger)
  message buses using typed classes, allowing for easier message dispatching and clean separation of commands, queries,
  and events.

## Install

Use [Composer](https://getcomposer.org/) to install the bundle.

```bash
composer require fusonic/framework-bundle
```

Requirements:
- PHP 8.2+
- Symfony 6.4+

In case Symfony did not add the bundle to the bundle configuration, add the following (by default, located in
`config/bundles.php`):

```php
<?php

return [
    // ...
    Fusonic\FrameworkBundle\FusonicFrameworkBundle::class => ['all' => true],
];
```

## Configuration

The bundle assumes that the [`symfony/messenger`](https://github.com/symfony/messenger) bus services IDs for the
command, event, and query buses are as follows:
- `command.bus`
- `event.bus`
- `query.bus`

If this is not the case, you can freely configure the service IDs by creating a new
`config/packages/fusonic_framework.php` file (or an equivalent YAML file) as follows:

```php
<?php

use Symfony\Config\FusonicFrameworkConfig;

return static function (FusonicFrameworkConfig $frameworkConfig): void {
    $frameworkConfig
        ->messenger()
            ->bus()
                ->commandBus('command.bus.with.other.name');
                ->eventBus('event.bus.with.other.name')
                ->queryBus('query.bus.with.other.name');
};
```

## Contributing

This is a subtree split of the [fusonic/php-extensions](https://github.com/fusonic/php-extensions) repository. Please
create your pull requests there.
