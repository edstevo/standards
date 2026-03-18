# Changelog

All notable changes to `edstevo/protocol` will be documented in this file.

## 1.0.19 - 2026-03-18

### Added
- Added an `execution-plans` skill with detailed ExecPlan authoring and implementation guidance for complex features and significant refactors.

### Changed
- Added core guideline guidance to require ExecPlans for complex features and significant refactors.
- Replaced the `.agent/PLANS.md` reference in the shared guideline entry with the new `execution-plans` skill.

## 1.0.18 - 2026-03-18

### Added
- Added a `github-issue-brief-refinement` skill for reviewing GitHub issues and rough briefs against the codebase, then rewriting them into implementation-ready briefs with affected files, key tests, and explicit open questions.

### Changed
- Updated delivery workflow guidance to require activating the new issue-brief refinement skill before implementation when work starts from an underspecified GitHub issue or brief.

## 1.0.17 - 2026-03-16

### Changed
- Strengthened Laravel testing guidance to prefer workflow-oriented tests that read like domain documentation, keeping graph construction, scenario evolution, the business act, and the resulting domain graph visible when that improves readability.
- Expanded `laravel-tdd` guidance on helper extraction, shared builder usage, and domain-graph assertions so local narrative clarity is preferred over technically reusable abstractions that hide the workflow.
- Clarified that Pest chained and scoped expectations can be used as a readability tool for nested domain assertions, not just as a lower-level assertion mechanism.

## 1.0.16 - 2026-03-13

### Added
- Added a `laravel-tdd` reference for time-sensitive tests covering Laravel time travel helpers, clearer clock-control patterns, and when to use scoped time-freezing helpers.

### Changed
- Updated shared testing guidance to discourage stacking multiple `$this->travelTo(...)` calls during arrange with no behaviour between them, and to prefer deriving timestamps separately or travelling immediately before each act.
- Updated the main `laravel-tdd` skill to surface the new time-testing guidance directly from its reference guide and default testing rules.

## 1.0.15 - 2026-03-12

### Added
- Added a new `laravel-actions` skill covering `lorisleiva/laravel-actions` conventions for one-class-one-task action classes, `AsAction` usage, `::run(...)` call sites, and action-specific mocking and spying in tests.

### Changed
- Updated shared Laravel guidance to prefer Laravel Actions for application action classes while keeping queued and asynchronous classes on native Laravel Jobs and Queues.
- Updated the package README summary to reflect the new Laravel Actions preference and the explicit action-vs-job split.

## 1.0.14 - 2026-03-11

### Changed
- Added shared delivery-workflow guidance requiring larger refactors, project-sized work, and issue fixes to be tracked in a pull request with an upfront plan and checklist, then merged back into the project's `dev` branch after approval.

## 1.0.13 - 2026-03-09

### Changed
- Added reusable guidance for deterministic scenario/test-data builders, including explicit graph-shaping flags, builder result usage, and group-level setup patterns.
- Expanded Laravel workflow/E2E testing guidance to cover positive-vs-defensive grouping, shared post-act `beforeEach()` setup, and preferring persisted outcomes over faking internal domain work in true end-to-end tests.
- Refined test-structure and shared-support references with more reusable, project-agnostic examples and a clearer static-analysis pattern after `toBeInstanceOf(...)` assertions.

## 1.0.12 - 2026-03-09

### Changed
- Softened the new redundant-test-defensiveness guidance so it distinguishes between production fail-fast guards and test code, steering tests toward one clear failure path without implying that defensive production code is undesirable.

## 1.0.11 - 2026-03-09

### Changed
- Expanded the `laravel-tdd` skill from a lightweight overview into a fuller Laravel/Pest testing guide with explicit red/green/refactor flow, test-layer selection, and behavioural test-design rules.
- Added load-on-demand `laravel-tdd` references for workflow/E2E refactors, shared test support, grouped `describe()` structure, and factory-driven test data.
- Strengthened testing guidance to prefer Pest `expect()` chains over `PHPUnit\Framework\Assert` in Pest tests while still allowing Laravel-native assertions where appropriate.
- Clarified that helper functions inside test files are a last resort and that shared test logic should prefer `tests/TestCase.php`, `tests/Support`, factory states, or richer factory configuration.
- Added stronger defaults for splitting bloated workflow tests by responsibility and for using factories and factory hooks (`state()`, `configure()`, `afterMaking()`, `afterCreating()`) to keep tests small and behavioural.

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
