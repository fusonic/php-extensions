# PHP Extensions

## Packages

The `packages` directory contains the source code for the packages. Everything in here will be published and available
to the public (For example, Gitlab CI related files or internal documentation must not be in here!)

### Available packages

- [assert](packages/assert/README.md)
- [api-documentation-bundle-bundle](packages/api-documentation-bundle/README.md)
- [csv-reader](packages/csv-reader/README.md)
- [ddd-extensions](packages/ddd-extensions/README.md)
- [framework-bundle](packages/framework-bundle/README.md)
- [http-kernel-bundle](packages/http-kernel-bundle/README.md)
- [messenger-mailer-bundle](packages/messenger-mailer-bundle/README.md)
- [sentry-cron](packages/sentry-cron/README.md)

## Development

A Docker container with PHP and Composer is included.
To run it execute `./docker/php-cli/run.sh` or `./docker/php-cli/run.sh packages/<package name>` to set a given
package as the working directory.

## Tests

The `ci/packages/<package name>` Gitlab CI file should contain the tests for a package. The shared job templates are
located in the `ci/templates/*.yml` files.

Each package defines its lowest supported PHP version in `PHP_LOWEST_VERSION` (must match `require.php` of the
package's `composer.json`). PHP-CS-Fixer, PHPStan, Rector, Infection and PHPUnit (incl. coverage) run on this version,
PHPUnit additionally runs with the lowest dependencies and on the latest PHP version (`PHP_LATEST_VERSION` in
`.gitlab-ci.yml`). Test jobs only run if the package or the CI definitions have changed.

The jobs use a prebuilt CI image (`ci` target of `docker/php-cli/Dockerfile`) from the project's container registry,
built by `build:php:image` for every PHP version listed in `ci/build.yml`. The image is rebuilt by scheduled pipelines
and on the default branch when the Dockerfile or the CI definition change, and can be built manually in such merge
requests. When raising a package's `PHP_LOWEST_VERSION` or the `PHP_LATEST_VERSION`, make sure the version is listed
in `ci/build.yml`.

## Publishing

Each package must have a public Git repository to which the source will be published.

The changes of packages merged into the master branch will automatically be published (as `dev-master`). In order to publish a new
version, make sure to increase the version inside the `composer.json` file of the package. Once this is merged a manual
`publish` pipeline step can be triggered.

Publishing stages for individual packages are defined in `ci/packages/<package name>` Gitlab CI file.

# CI Pipelines

Required CI/CD variables:
* `GIT_EMAIL`: E-Mail address used to push to the public package Git repository.
* `GIT_NAME`: Name used to push to the public package Git repository.
* `SSH_PRIVATE_KEY`: Private SSH key for pushing to the public Git repository.

