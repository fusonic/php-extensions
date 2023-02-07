# UPGRADE

## UPGRADE FROM 0.0.7 to 0.0.8

- `IdTrait` got renamed to `IntegerIdTrait`
- `AggregateRoot` does not implement the `IdTrait` anymore
- `EntityInterface` changed the return type of `getId` to mixed

### Reasoning

We want more flexibility when using ids and the types they do represent. With the changes mentioned above, we can now
leave the decision regarding the type of id to the user of this extension. Meaning now you have the responsibility but
also possibility to implement the `getId` function. It can be an `integer`, a `Uuid`, a customer id type or something 
completely different.
