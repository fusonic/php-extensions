# UPGRADE

## UPGRADE FROM 1.5.0 to 2.0.0

- `ValueObject` and the extending classes `EntityId` and `EntityIntegerId` are now readonly (new PHP 8.2 feature)

## UPGRADE FROM 1.0.1 to 1.1.0

- `DomainEventSubscriber` has been marked as deprecated, `DomainEventLifecycleListener` has been introduced as
  replacement

### Reasoning

Lifecycle subscribers are deprecated starting from Symfony 6.3 and will be removed in Symfony 7.0 (see
https://symfony.com/doc/6.3/doctrine/events.html#doctrine-lifecycle-listeners).

## UPGRADE FROM 0.0.7 to 0.0.8

- `IdTrait` got renamed to `IntegerIdTrait`
- `AggregateRoot` does not implement the `IdTrait` anymore
- `EntityInterface` changed the return type of `getId` to mixed

### Reasoning

We want more flexibility when using ids and the types they do represent. With the changes mentioned above, we can now
leave the decision regarding the type of id to the user of this extension. Meaning now you have the responsibility but
also possibility to implement the `getId` function. It can be an `integer`, a `Uuid`, a customer id type or something
completely different.
