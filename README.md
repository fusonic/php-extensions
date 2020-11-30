# PHP Extensions

## Packages

The `packages` directory contains the source code for the packages. Everything in here will be published and available
to the public (For example Gitlab CI related files or internal documentation must not be in here!)

### Available packages

- [csv-reader](packages/csv-reader/README.md)
- [http-kernel-extensions](packages/http-kernel-extensions/README.md)

## Development

A Docker container with PHP and Composer is included.
To run it execute `./docker/php-cli/run.sh` or `./docker/php-cli/run.sh packages/<package name>` to set a given
package as the working directory.

## Tests

The `ci/packages/<package name>` Gitlab CI file should contain the tests for a package.

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

