# PHP Extensions

## Packages

The `packages` directory contains the source code for the packages. Everything in here will be published and available
to the public (For example Gitlab CI related files or internal documentation must not be in here!)

## Tests

The `gitlab-ci/tests.yml` file contains tests. Unit tests for packages are also included here.

## Publishing

The changes of packages merged into the master branch are automatically published (as `dev-master`). In order to publish a new
version, make sure to increase the version inside the `composer.json` file of the package. Once this is merged a manual
`publish` pipeline step can be triggered.

## Development

A Docker container with PHP and Composer is included.
To run it execute `./docker/php-cli/run.sh` or `./docker/php-cli/run.sh package/<package name>` to set a given
package as the working directory.

