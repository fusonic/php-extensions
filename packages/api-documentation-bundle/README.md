# fusonic-api-documentation-bundle

[![License](https://img.shields.io/packagist/l/fusonic/api-documentation-bundle?color=blue)](https://github.com/fusonic/php-api-documentation-bundle/blob/master/LICENSE)
[![Latest Version](https://img.shields.io/github/tag/fusonic/php-api-documentation-bundle.svg?color=blue)](https://github.com/fusonic/php-api-documentation-bundle/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/fusonic/api-documentation-bundle.svg?color=blue)](https://packagist.org/packages/fusonic/api-documentation-bundle)
![php 8.0+](https://img.shields.io/badge/php-min%208.0-blue.svg)

* [About](#about)
* [Install](#install)
* [Usage](#usage)
* [Contributing](#contributing)

## About

This bundle makes generating documentation of your (json) API routes easier. It provides a custom route annotation that
can parse the input and output model of a route to generate documentation definitions for
[NelmioApiDocBundle](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html). If you are using
type hints for the input and output it can be detected automatically, see [Usage](#Usage) on how to do this.

With just [NelmioApiDocBundle](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html) you will often find yourself
writing many annotations with a repetitive pattern. With this bundle common annotation combinations are bundled into one
single route attribute.

This bundle can work well together with
the [http-kernel-extensions](https://github.com/fusonic/php-http-kernel-extensions).

## Install

Use composer to install the bundle from packagist.

```bash
composer require fusonic/api-documentation-bundle
```

Requirements:

- PHP 8.1+
- Symfony 5.4+

In case Symfony did not add the bundle to the bundle configuration, add the following (by default located
in `config/bundles.php`):

```
<?php

return [
    // ...
    Fusonic\ApiDocumentationBundle\FusonicApiDocumentationBundle::class => ['all' => true],
];
```

Next you need to configure [NelmioApiDocBundle](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html).

There is one optional configuration for this bundle:

```yaml
fusonic_api_documentation:
    # An attribute, class, or interface that is used to detect which object to parse the "input" model from.
    # If you do not configure this, automatic input detection will not work.
    request_object_class: Fusonic\ApiDocumentationBundle\Tests\App\FromRequest
```

## Usage

Different examples can be found in the [tests](./tests/App/Controller/TestController.php).

#### Example route with automatic type detection

If you have some kind of response listener that allows you to return objects directly from your controller then you
can use the automatic output detection based on the return type or return annotation.

```php
    #[DocumentedRoute(path: '/test-return-type/{id}', methods: ['GET'])]
    public function testReturnType(#[FromRequest] TestRequest $query): TestResponse
    {
        return new TestResponse($query->id);
    }
```

If you return an array or a generic type, you can set the return type (e.g.: `SomeType[]` or `SomeGeneric<SomeType>).
The only requirement here is that you can only have one return type. Multiple return types are not supported.

#### Example route with manual input/output

If you do not support argument resolving and returning objects directly you can define the `input` and `output`
classes manually.

```php
    #[DocumentedRoute(path: '/test-return-type/{id}', methods: ['GET'], input: TestRequest::class, output: TestResponse::class)]
    public function testReturnType(int $id): JsonResponse
    {
    return new JsonResponse(['id' => $query->id], 200);
    }
```

You can also specify builtin types for the output, for example `string`:

```php
#[DocumentedRoute(path: '/test-return-type/{id}', methods: ['GET'], input: TestRequest::class, output: 'string')]
```

If your manually defined output is a collection, you can set `outputIsCollection: true` in addition to the `output`:

```php
#[DocumentedRoute(
    path: '/test-return-type/{id}',
    methods: ['GET'],
    input: TestRequest::class,
    output: 'string',
    outputIsCollection: true
)]
```

## Contributing

This is a subtree split of [fusonic/php-extensions](https://github.com/fusonic/php-extensions) repository. Please create
your pull requests there.
