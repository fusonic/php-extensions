# Upgrade to v2.0

## Requirements
- Bumped the required Symfony version from `^6.4 || ^7.3 || ^8.0` to `^7.3 || ^8.0`
- Added `symfony/type-info` as a dependency

## Changes
- The existing `StrictRequestDataCollector` was renamed to `RequestDataCollector`. Its behaviour did not change and it
  is still used by default.
- The new `StrictRequestDataCollector` converts route parameters, query parameters and form request bodies to the types
  of the DTO and reports invalid values as a `ConstraintViolationException`. Enable it with the `strict` configuration, see the
  [documentation](docs/extensions/request-dto-resolver.md#route-and-query-parameters).
- `RequestDataCollectorInterface::collect()` has a new `$className` parameter
- `RequestDataCollectorInterface` has a new `getDenormalizationContext()` method. Disabling the type enforcement for
  requests without a body moved from the `RequestDtoResolver` to the `RequestDataCollector`.
- `RequestDtoResolver::METHODS_WITH_STRICT_TYPE_CHECKS` is deprecated, use
  `RequestDataCollectorInterface::METHODS_WITH_REQUEST_BODY` instead
- The `$modelDataParser` constructor parameter of `RequestDtoResolver` was renamed to `$requestDataCollector`
