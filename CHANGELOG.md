# Changelog

All notable changes to `edstevo/protocol` will be documented in this file.

## 1.0.9 - 2026-03-03

### Changed
- Added core baseline guidance for Laravel-native integration boundary fakes, including fake-first testing posture and contract/facade expectations.
- Strengthened core documentation policy to require process-doc updates alongside workflow behavior changes and to explicitly activate `process-documentation-with-flowcharts`.
- Standardized process documentation flowchart notation to use Mermaid `flowchart TB`.
- Added core baseline guidance for reusable Eloquent relationship traits and explicit activation guidance for `eloquent-relationship-traits`.

## 1.0.8 - 2026-03-03

### Changed
- Reduced `core.blade.php` event-driven guidance to concise defaults and added explicit direction to activate `model-events-observers-workflows` for full implementation detail.
- Expanded `model-events-observers-workflows` skill with detailed lifecycle patterns for `saveQuietly()` transitions, transaction + after-commit semantics, and isolated model event testing strategy.
- Clarified observer orchestration rules: observers should not run synchronous external follow-up actions inline; they should fire events, dispatch jobs, or call methods that trigger further explicit model events.
- Updated skill examples to use dispatched jobs/events for timeline and integration follow-up work rather than direct synchronous observer execution.

## 1.0.7 - 2026-03-03

### Changed
- Simplified package bootstrap by removing command registration from `StandardsServiceProvider`.
- Reduced package dependencies in `composer.json` to only require PHP and removed development/autoload-dev package wiring.

### Removed
- Removed `edstevo:standards` command implementation.
- Removed package test scaffolding files under `tests/`.
- Removed Dependabot configuration.

## 1.0.6 - 2026-03-03

### Added
- Added `filament-supplier-portals` skill for Filament multi-panel apps with role-aware login/logout redirects, panel guard middleware, and `canAccessPanel` authorization guidance.
- Added `laravel-integration-fakes` skill for building Laravel-native integration fakes using contracts, container bindings, facade swaps, and assertion-friendly in-memory recorders.
- Added `laravel-tdd` skill for Laravel-specific TDD workflows with Pest, database testing patterns, and red-green-refactor execution.

## 1.0.5 - 2026-03-02

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
