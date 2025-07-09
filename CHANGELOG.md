# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),  
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- (Describe new features here)

### Changed
- (Describe changes here)

### Fixed
- (Describe bug fixes here)

---

## [3.0.0] - 2025-07-08

### Added
- New RESTful Client class for improved API communication
- Domain registration functionality with contact management
- Bulk search capabilities
- Enhanced DTOs for better data handling
- PHP CS Fixer configuration for coding standards
- Comprehensive DTO serialization tests

### Changed
- API is now based on the new RESTful API from Dynadot
- Replaced deprecated `DynadotApi` class with new `Client` class
- Updated all example scripts to use the new RESTful API client
- Enhanced README with new API usage instructions
- DomainRegistrationRequest now uses contact IDs instead of direct Contact objects
- Removed unused `sabre/xml` dependency

---

## [2.0.4] - 2025-04-25
### Fixed
- Bugfix for NameServerSettings

## [2.0.3] - 2025-04-25
### Added
- PHP 8.4 compatibility

## [2.0.2] - 2023-10-18
### Fixed
- Fixed nameservers in domain info response

## [2.0.1] - 2023-09-19
### Added
- Added implementation of set_renew_option method

## [2.0.0] - 2023-07-24
### Changed
- PHP 8.2 as minimum PHP version

## [1.0.2] - 2023-05-10
### Fixed
- Fixed dynadot API issue, no DomainInfoResponseHeader section is given

## [1.0.1] - 2023-04-25
### Fixed
- Fixed boolean value mismatch and fixed DomainInfoList response

## [1.0.0] - 2023-06-11
### Changed
- We now require PHP version 7.3 or higher

## [0.2.1] - 2023-01-23
### Fixed
- Fixed issue where nameservers were in an array (1 level too deep)

## [0.2.0] - 2023-01-19
### Changed
- Rewritten API and better unit tests. Basis is now OK

--- 