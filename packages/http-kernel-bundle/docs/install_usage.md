# Install & Usage

* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)

## Requirements

Please see the table below for an overview of supported PHP and Symfony versions:

| **Bundle version** | **Supported PHP version** | **Supported Symfony version** |
|--------------------|---------------------------|-------------------------------|
| ^1.0.0             | \>= 8.2                   | \>= 6.3                       |

## Installation

Use [Composer](https://getcomposer.org/) to install the bundle from
[Packagist](https://packagist.org/packages/fusonic/http-kernel-bundle):

```shell
composer require fusonic/http-kernel-bundle
```

In case Symfony Flex did not add the bundle to the bundle configuration (by default located in `config/bundles.php`),
add the following line:

```php
<?php

declare(strict_types=1);

return [
    # ...
    Fusonic\HttpKernelBundle\HttpKernelBundle::class => ['all' => true],
];
```

## Usage

For detailed documentation on how to use the individual extensions for Symfony's
[HttpKernel component](https://symfony.com/doc/current/components/http_kernel.html), please refer to the extension
documentations located in the [extensions](./extensions) folder:
- [RequestDtoResolver documentation](./extensions/request-dto-resolver.md)
