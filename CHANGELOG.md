# Changelog

All notable changes to `edstevo/protocol` will be documented in this file.

## 1.0.4 - 2026-03-02

### Added
- Added `process-documentation-with-flowcharts` skill for maintaining workflow documentation with Mermaid top-down flowcharts, strict entry-point semantics, and synchronized key files/tests updates.

## 1.0.3 - 2026-03-01

### Changed
- Renamed Boost skill files to `Skill.md` within dedicated skill directories for improved naming consistency.
- Updated `model-events-observers-workflows` skill content following naming adjustments.

## 1.0.2 - 2026-03-01

### Added
- Added `eloquent-relationship-traits` skill with conventions and templates for reusable Eloquent relationship traits.
- Added `model-events-observers-workflows` skill with guidance for explicit model events, after-commit observers, and workflow orchestration patterns.

## 1.0.1 - 2026-02-19

### Changed
- Expanded AI guideline instructions to be more explicit and execution-focused.
- Added coding style defaults reflecting domain-first naming, explicit orchestration, and defensive patterns.
- Added model construction and type-safety guidance, including explicit Eloquent construction and `associate()` usage.

### Removed
- Removed PHPStan/Larastan from project scaffolding and package configuration.
- Removed PHPStan configuration files and static-analysis command documentation.

## 1.0.0 - 2026-02-11

### Added
- Initial release of EdStevo Standards
- Laravel Boost integration with automatic guideline discovery
- Comprehensive AI guidelines for:
  - Project structure conventions
  - Code style and best practices
  - Testing standards with Pest PHP
- ProtocolCommand for verifying installation
- Zero-configuration setup
- Support for Laravel 11.x and 12.x
- Support for PHP 8.4+

### Guidelines Included
- Domain-based directory organization
- Action classes pattern
- Data Transfer Objects (DTOs)
- Service classes pattern
- Native PHP enums usage
- Thin controller approach
- Query optimization guidelines
- Security best practices
- Pest PHP testing patterns
