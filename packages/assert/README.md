# assert

[![License](https://img.shields.io/packagist/l/fusonic/assert?color=blue)](https://github.com/fusonic/php-assert/blob/master/LICENSE)
[![Latest Version](https://img.shields.io/github/tag/fusonic/php-assert.svg?color=blue)](https://github.com/fusonic/php-assert/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/fusonic/assert.svg?color=blue)](https://packagist.org/packages/fusonic/assert)
[![php 8.2+](https://img.shields.io/badge/php-min%208.2-blue.svg)](https://gitlab.com/fusonic/devops/php/extensions/-/blob/12-open-source-preparations/packages/assert/composer.json)

* [About](#about)
* [Install](#install)
* [Usage](#usage)

## About

This assertion library extends [beberlei/assert](https://github.com/beberlei/assert) with convenient chaining functionality
to improve error message details.

* Wraps all chained and non-chained assertion errors in the same exception object.
* Lazy validation with a defined root path (e.g. an object class name)
* Single assertions with a separate root path as a first argument

## Install

Use composer to install the library from packagist.

```bash
composer require fusonic/assert
```

## Usage 

Regular assertion with root path:

```php
Assert::that('User', 'username', $username)->notEmpty()->length(10);
```

Chained lazy assertion with root path example:

```php
Assert::lazy('User')
    ->that($username, 'username')
        ->notEmpty()
    ->that($password, 'password')
        ->minLength(8)
        ->maxLength(30)
    ->verifyNow();
```

Example output:

```
The following 2 assertions failed:
1) User.username: Value "" is empty, but non empty value was expected.
2) User.password: Value "1123" is too short, it should have at least 8 characters, but only has 4 characters.
```
