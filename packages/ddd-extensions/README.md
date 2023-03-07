# ddd-extensions

[![License](https://img.shields.io/packagist/l/fusonic/ddd-extensions?color=blue)](https://github.com/fusonic/php-ddd-extensions/blob/master/LICENSE)
[![Latest Version](https://img.shields.io/github/tag/fusonic/php-ddd-extensions.svg?color=blue)](https://github.com/fusonic/php-ddd-extensions/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/fusonic/ddd-extensions.svg?color=blue)](https://packagist.org/packages/fusonic/ddd-extensions)
[![php 8.0+](https://img.shields.io/badge/php-min%208.0-blue.svg)](https://gitlab.com/fusonic/devops/php/extensions/-/blob/12-open-source-preparations/packages/ddd-extensions/composer.json)

* [About](#about)
* [Install](#install)
* [Configuration](#configuration)
* [Usage and recommendations](#usage-and-recommendations)

## About

This library provides some base classes for implementing a domain driven design in PHP. Helpful tools are provided
for usage with Symfony and Doctrine, however you are not required to use those.

## Install

Use composer to install the lib from packagist.

```bash
composer require fusonic/ddd-extensions
```

## Configuration

```yaml
services:
    # This service dispatches the domain events raised on aggregate roots to the given message bus.
    Fusonic\DDDExtensions\Doctrine\EventSubscriber\DomainEventSubscriber:
        arguments:
            $bus: '@Symfony\Component\Messenger\MessageBusInterface'
            $logger: '@logger' # optional
        tags:
            - { name: doctrine.event_subscriber }

    # Optionally configure a ModelDescriber if you are using NelmioApiDocBundle to display EntityId objects in the 
    # generated documentation
    Fusonic\DDDExtensions\ModelDescriber\EntityIdDescriber:
        tags:
            - { name: nelmio_api_doc.model_describer, priority: 1000 }
    
    # Optionally configure a normalizer to normalizer to automatically serialize/deserialize AbstractIntegerId objects
    Fusonic\DDDExtensions\Normalizer\EntityIdNormalizer:
        tags:
            - { name: serializer.normalizer }
```

## Usage and recommendations

For an example, see the [examples in the tests](./tests/Domain).

### Value objects
Value objects must extend `Fusonic\DDDExtensions\Domain\Model\ValueObject`. Value objects must be immutable.
All the properties that it needs must be set when initiating the object.
The value object can not have any setter properties.

For comparing value objects you must implement the `equals` function.

### Aggregate roots

Aggregate roots are the *entry points* to the bounded context. Domain objects that extend `Fusonic\DDDExtensions\Domain\Model\AggregateRoot`
are aggregate roots. Only the aggregate roots can be created/modified directly outside of the bounded contexts.
All sub-entities are modified/created through the aggregate root.

### Domain entities
Domain entities must implement `Fusonic\DDDExtensions\Domain\Model\EntityInterface`.

You should not do operations on domain entities (that are not aggregate roots) directly. Everything should
go through the aggregate root.

The "id" returned by `getId()` can return anything, however it is recommended to create a dedicated "id" class for each domain entity. For example a `User` class with a `UserId` class.
The `EntityId` extended class must implement a `__toString` method and a `getValue()` method which will return the internal value.
The implementation of the internal
value is up to you. For Doctrine you could use an integer and use the `Fusonic\DDDExtensions\Domain\Model\EntityIntegerId` class
as a base class, see [this example](./tests/Domain/UserId.php).

In order to have consistent return types and to avoid null-checks everywhere, you cannot return null. If you
use the `AbstractIntegerId` base class, the default internal value will be `0`. To check for this a convenient `isDefined()`
method is implemented.

```php

public function getId(): UserId
{
    return new UserId($this->id);
}
```

### Assertions

The domain layer does not depend on any validation services. Validation logic has to be inside the models themselves.
For assertions in the domain there is a static helper class with common assertion functions.
See: `Fusonic\DDDExtensions\Domain\Validation\Assert`.

### Domain exceptions

Domain exceptions can only be thrown from within the domain. All domain exceptions must implement
the `Fusonic\DDDExtensions\Domain\Exception\DomainExcetionInterface`.

### Domain events
Domain objects that extend `Fusonic\DomainDrivenDoctrin\Domain\Model\AggregateRoot` can
raise events. Inside the class you can call `$this->raise(...)` with an event that implements
`Fusonic\DDDExtensions\Domain\Event\DomainEventInterface`.

All raised domain events will be dispatched when Doctrine `flush` is called.
The `Fusonic\DDDExtensions\Doctrine\EventSubscriber\DomainEventSubscriber` handles this.

### ORM Mapping
You must not use PHP annotations or attributes for defining your ORM mapping. Mapping should be configured outside of
the domain. For Doctrine, you can use XML or PHP mapping.

### Value objects as embeddables
Value object mapping can be done using Doctrine embeddables, but only if it is a one-to-one relation.
The advantage of embeddables is that you can use the Doctrine query language to query the fields easily.

### Value objects as JSON
Another way to map value objects is to define a custom Doctrine type. Extend the `Fusonic\DDDExtensions\Doctrine\Type\ValueObjectType`
and implement the `convertToDatabaseValue` and `convertToPHPValue` methods to define the mapping for a value object. The class provides
four helper methods for serialization: `serialize`, `deserialize` and `serializeArray`, `deserializeArray` in case you want to store
and array of the value objects (one-to-many relation). 
Example [here](./tests/Doctrine/Types/AddressValueObjectType.php) and [here](./tests/Doctrine/Types/AddressValueObjectCollectionType.php).
Inside the database the objects will be stored as `json`.

After implementing the custom type you need to [register it](https://symfony.com/doc/current/doctrine/dbal.html#registering-custom-mapping-types).

Querying for json data is not possible out-of-the-box in Doctrine,
it is however possible using extensions ([example](https://github.com/ScientaNL/DoctrineJsonFunctions)).
