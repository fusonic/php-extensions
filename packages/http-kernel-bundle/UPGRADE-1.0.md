# Upgrade from `fusonic/http-kernel-extensions` to v1.0

## Requirements
- Bumped the required PHP version from `>= 8.1` to `^8.2`
- Bumped the required Symfony version from `^5.4|^6.0` to `^6.3`  

## Changes
- Namespaces of all classes have changed from `Fusonic/HttpKernelExtensions` to `Fusonic/HttpKernelBundle`

## Configuration
- Manually registering and tagging the `RequestDtoResolver` is not necessary anymore
- Manually registering and tagging the `ConstraintViolationExceptionNormalizer` is not necessary anymore
- Manually tagging the `ContextAwareProviderInterface`  is not necessary anymore
