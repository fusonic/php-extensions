# Upgrade to v2.0

## Requirements
- Bumped the required Symfony version from `^6.4 || ^7.3 || ^8.0` to `^7.3 || ^8.0`
- Added `symfony/type-info` as a dependency

## Changes
- Route and query parameters are converted to the types of the DTO properties. See the
  [documentation](docs/extensions/request-dto-resolver.md#route-and-query-parameters) for the `strict_route_params` and
  `strict_query_params` configuration.
- `RequestDataCollectorInterface::collect()` has a new `$className` parameter
- The constructor of `StrictRequestDataCollector` has changed. The `$forceRouteParamsIntegers` parameter was removed,
  route parameters are now converted based on the types of the DTO.
- `RequestDtoResolver::METHODS_WITH_STRICT_TYPE_CHECKS` was replaced by
  `StrictRequestDataCollector::METHODS_WITH_REQUEST_BODY`
- The `$modelDataParser` constructor parameter of `RequestDtoResolver` was renamed to `$requestDataCollector`
- Type enforcement of the serializer is no longer disabled for requests without a body
- Removed the unused `TypeConstraintViolation`
