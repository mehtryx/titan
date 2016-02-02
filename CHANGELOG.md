# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased][unreleased]
- Caching testing
- Updated to latest ci-template files

## [1.0.0] - 2015-07-01
### Added
- Eaten by a grue test case

### Changed
- Increased major version to 1.0.0 for production release, no longer alpha (0.x) code
- Test case for example was made to assert false is true, this will fail

### Removed
- TestSample3() which was marked deprecated in 0.9.0 and has been deprecated for greater than 30 days


## [0.9.2] - 2015-06-25
### Fixed
- Typo in installer script caused removal of temp files to fail.

### Deprecated
- TestSample3() marked deprecated in 0.9.0 but not for more than 30 days


## [0.9.1] - 2015-06-09
### Fixed
- Version specified for eslint in package.json was not compatible with node.js version used, now set correctly

### Deprecated
- TestSample3() marked deprecated in 0.9.0 but not for more than 30 days


## [0.9.0] - 2015-05-30
### Added
- additional test cases to show multiple failures on output

### Fixed
- fixed fixture function to include parent::setUp() call, allows factory class to work as expected
- notifications email in .travis.yml configuration file

### Changed
- Moved phpunit.xml to be pulled entirely from CI_Config project
- Moved codesniffer.ruleset to be pulled from the CI_Config project

### Deprecated
- TestSample3() test is a duplicate of the other examples, it has no actual value and has been marked for removal, remove after standard 30 days.

### Security
- Added check that execution path of script and path used for rm commands does not result in accidental deletion on root path.
- Removed requirement to supply mysql password on commandline which stores this in the bash history file

### Removed
- WCAG2AA test functionality which was marked deprecated in 0.8.0 and has been deprecated for greater than 30 days

## [0.8.1] - 2015-03-04
### Fixed
- Environment variable used for wordpress version was not being read, wrong path to file.

### Deprecated
- WCAG2AA test functionality marked deprecated in 0.8.0

## [0.8.0] - 2015-02-15
### Changed
- Installation of ESSLint and CSSLint now controlled by package.json file.

### Deprecated
- WCAG2AA testing functionality is not yet ready for automation, removing current functionality until this area matures.  Remove after standard 30 days as deprecated.
