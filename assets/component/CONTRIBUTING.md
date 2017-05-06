# Contributing

When contributing to this repository, please first discuss the change you wish to make via issue,
email, or any other method with the owners of this repository before introducing a change.

## Pull Request Process

1. Provide unit tests for all proposed changes.
1. Ensure the `test` and `static-analysis` build targets are passing, as the CI process will disallow merging if this build fails.
1. Update the CHANGELOG.md with details of changes to the library. The scheme we use is
   [Keep a Change Log](http://keepachangelog.com/)
1. If applicable, update the README.md file with examples of any added functionality.
1. Request the repo owner(s) merge the Pull Request for you.
1. Simple note that the versioning scheme we use is [SemVer](http://semver.org/).

## Code Standards

This package leans on several static analysis tools such as `phpmd` and `phpcs` to evaluate syntax,
as well as tools that measure code complexity, etc.

Please note that we insist on following the **PSR-2** standard of coding.

## Running the Build/Test Suite

This package makes extensive use of the [Phing](https://www.phing.info/ "Click to Learn More") build tool.

Below is a list of notable build targets, but please feel free to peruse the `build.xml` file for more insight.

### Default Target

`./vendor/bin/phing` will execute the `build` target (the same as executing `./vendor/bin/phing build`).
This performs a linting, syntax check, runs all static analysis tools, the test suite, and produces API documentation.

### Selected Individual Targets
- Run the Tests
    - `./vendor/bin/phing test`
    - `./vendor/bin/phpunit`
- Perform Static Analysis
    - `./vendor/bin/phing static-analysis`
    - The generated reports are in `./build/output/reports`
- Produce API Documentation
    - `./vendor/bin/phing documentapi`
    - The generated documentation is in `./build/docs/api`
- Build Package from Source
    - `./vendor/bin/phing package`
    - The artifacts are in `./build/output/artifacts`

### "Full" Packaging Target

Executing `./vendor/bin/phing package` will run all above checks and, if passing, package the source into a shippable file
with only the relevant source included therein.

## Code of Conduct

Please visit the [Contributor Covenant Code of Conduct](http://contributor-covenant.org/version/1/4/). 

## Attribution

This CONTRIBUTING file is adapted from the Gist of [PurpleBooth](https://gist.github.com/PurpleBooth/b24679402957c63ec426).