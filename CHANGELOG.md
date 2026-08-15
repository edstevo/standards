# Changelog

All notable changes to `edstevo/protocol` will be documented in this file.

## 1.0.73 - 2026-08-15

### Changed
- Distinguished dispatch-time freshness checks from substantive stage planning: approved-stage compatibility audits no longer consume the single planning-task slot, may run alongside planning for another stage, and cannot repeat guided review or reopen settled decisions.
- Added explicit freshness outcomes for automatic promotion, contained upstream correction while remaining provisional, and genuine approved-plan invalidation requiring renewed planning and human approval.

## 1.0.62 - 2026-08-13

### Changed
- Set explicit execution-plan model routing: `gpt-5.6-sol` at Ultra for overall plan and stage-map creation, `xhigh` for pre-stage planning gates, `high` for Laravel implementation, and `xhigh` for implementation review.
- Required every ExecPlan stage to be a small, isolated subplan with one coherent component, one implementation agent, one child PR, and focused verification; oversized stages must be split before implementation.
- Added versioned gate handoffs so planning, implementation, review, and closure carry forward certified context; downstream agents avoid rereading every stage or repeating upstream compatibility checks unless a relevant change explicitly invalidates the handoff.
- Allowed dependent stages to receive user-approved provisional reviews while prerequisites are active, followed by a lightweight `xhigh` delta gate when final upstream commits land; compatible deltas promote automatically, while material conflicts return to the user for renewed sign-off.

## 1.0.61 - 2026-08-12

### Changed
- Reworked execution plans around a searchable plan index, concise authoritative master plans, and a required detailed document for every stage, with stable naming, separate authoring and orchestration workflows, controller-owned parent PRs, Ultra stage-planning chats, Extra High implementation and review agents, scoped child stage PRs, and durable stage-closing updates.
- Aligned the core delivery workflow so ordinary PR checklists remain in PR bodies while formal ExecPlans use the indexed plan-document workflow.
- Required orchestration startup to discover and resume an existing parent PR and its latest plan-branch documents before creating any new execution branch or PR, preventing duplicate plan executions.

## 1.0.60 - 2026-08-12

### Changed
- Required all bug planning and user decisions to use the strongest intelligence model available at Ultra reasoning, while bug implementation and independent fix review use the strongest suitable model at Extra High reasoning, with future equivalent tiers replacing current Sol examples.

## 1.0.59 - 2026-08-12

### Changed
- Made approved bug scope a hard implementation and review boundary: fix agents must avoid additional audits and opportunistic work, stop and request user-confirmed scope expansion when necessary, and independent reviewers must fail changes that escape the approved scope.

## 1.0.58 - 2026-08-12

### Changed
- Required each user-facing bug decision side task to use the exact title `<BUG-ID> Review`, such as `BUG-260810-002 Review`.

## 1.0.57 - 2026-08-12

### Added
- Added an ordered review and implementation pipeline: approved bugs are marked `Implementation: Ready` while earlier fixes run, closing a prerequisite must discover and dispatch newly unblocked ready dependants, and implementation/review state remains visible in the buglist and investigation.

## 1.0.56 - 2026-08-12

### Added
- Added explicit bug dependencies with direct `Depends on` references, dependency-first controller ordering, cycle and missing-target checks, and dependency-aware disposition and cleanup rules.
- Added explicit `Release blocker:` targets so go-live bugs and their dependency chains are resolved before unrelated backlog bugs, without conflating release scope with severity.
- Required linked bug investigations to mirror direct dependencies and explain each prerequisite's rationale and completion condition in plain English.

## 1.0.55 - 2026-08-11

### Changed
- Made the main buglist task the long-lived controller, moved each bug's plain-English review and user decision into a read-only side task, and required that decision to be handed back before a separate implementation subagent may start.

## 1.0.54 - 2026-08-11

### Changed
- Made `buglist-triage` the exclusive workflow skill for creating, reviewing, deciding, fixing, independently reviewing, merging, and closing tracked bugs, with explicit disposition, test, documentation, and post-merge cleanup gates.
- Allowed projects to override the default date-sequence bug ID convention, including documented module-scoped IDs, without renumbering existing bugs.

## 1.0.53 - 2026-08-02

### Added
- Added Laravel migration guidance requiring concise explicit names for composite indexes and potentially long constraints so generated identifiers remain within database limits such as MySQL's 64-character maximum.

## 1.0.52 - 2026-05-18

### Added
- Added first-class Laravel Action Pattern guidance covering synchronous `lorisleiva/laravel-actions` action classes, `AsAction`/`::run(...)` usage, action naming and boundaries, transactions, testing, and relationships with state, strategy, events, observers, and jobs.
- Added Laravel DTO Pattern guidance standardizing structured application data on `spatie/laravel-data`, explicit typed `Data` classes, request-to-DTO-to-action flow, AI/static-analysis benefits, DTO naming, and array-avoidance boundaries.
- Added Factory and Manager Pattern guidance covering centralized object creation, Laravel-style driver/provider/connection selection, strategy and adapter selection, DTO/client boundaries, and relationships with actions, builders, state, policies, and integrations.
- Added Specification Pattern guidance covering reusable side-effect-free boolean business rule checks, eligibility logic, composition, testing, and relationships with actions, states, strategies, policies, observers, DTOs, factories, and managers.

### Changed
- Reorganized the Laravel coding-style skill into a concise decision map with pattern boundaries, keeping detailed guidance in focused references for easier AI-agent navigation.
- Clarified the action/job split: actions are synchronous and are the only classes that use the Laravel Actions package, while jobs are asynchronous native Laravel queued work units dispatched through Laravel framework conventions for Horizon processing.
- Clarified queued job conventions to pass Eloquent models directly when they are the payload and to name native job classes without a `Job` suffix.
- Refined Observer Pattern guidance to require extremely shallow observers, prohibit private workflow helpers and second-order functions, and route business rules to models, actions, specifications, strategies, policies, or state classes.

## 1.0.51 - 2026-05-18

### Added
- Added Laravel Strategy Pattern guidance covering contract-backed interchangeable algorithms, concrete strategies, resolver/config-map/container selection, Laravel naming boundaries, state-pattern distinction, and focused testing expectations.

## 1.0.50 - 2026-05-18

### Added
- Added Laravel State Pattern guidance standardizing complex workflows on `spatie/laravel-model-states`, with focused references for state classes, transition classes, multiple state dimensions, transition context, before/after model events, policies, and testing.

## 1.0.49 - 2026-05-16

### Added
- Added PHP/Laravel builder pattern guidance covering builders as the default for complex construction, when to avoid them, terminal method semantics, validation, mutability, and testing expectations.

### Changed
- Updated core and Laravel coding-style guidance to introduce focused builders as soon as object, DTO, model graph, report, import, command, filter, or workflow construction becomes complex, option-heavy, multi-step, or hard to read, without waiting for repeated usage.

## 1.0.47 - 2026-05-15

### Changed
- Tightened Laravel Actions versus Jobs guidance so `lorisleiva/laravel-actions` classes are reserved for synchronous reusable behavior, while queued, delayed, retryable, or asynchronous work must use native Laravel jobs that may call actions from `handle(...)`.

## 1.0.46 - 2026-05-14

### Changed
- Clarified that "reconcile the buglist" means a narrow audit-and-alignment pass over active entries, duplicates, grouping, priorities, and linked investigations without fixed-entry cleanup, PR Agent movement, bug splitting, or implementation verification.

## 1.0.45 - 2026-05-13

### Changed
- Updated buglist investigation guidance to include a `Scope` section for investigation and likely fix boundaries.
- Updated Laravel queue guidance to prefer `ShouldQueueAfterCommit` over repeated `->afterCommit()` dispatch chains when the job can own after-commit semantics.

## 1.0.43 - 2026-05-13

### Added
- Added a Codex `New Release` environment action backed by a project-local release script.

### Changed
- Updated buglist triage cleanup guidance so fixed bug removals also delete linked investigation files.

## 1.0.42 - 2026-05-11

### Added
- Added a `buglist-triage` skill for maintaining concise buglist entries, linked investigation files, and PR Agent handoff references.

## 1.0.41 - 2026-05-09

### Changed
- Added a `Scope` section to `pr-agent-prompts` for explicitly listing work that is not in scope.

## 1.0.40 - 2026-05-09

### Changed
- Renamed `github-issue-briefs` to `pr-agent-prompts` and simplified the workflow to produce local three-section PR Agent prompts instead of updating GitHub issues.

## 1.0.39 - 2026-05-08

### Changed
- Tightened app knowledge maintenance guidance to keep always-loaded docs small, avoid duplicated skill/doc content, and route deeper documentation layout rules through a focused reference.

## 1.0.38 - 2026-05-07

### Changed
- Updated core, Laravel TDD, Filament, SOLID, and model lifecycle testing guidance to prefer small scenario-focused test files over large mixed suites, especially for workflow, integration, and E2E coverage.

## 1.0.37 - 2026-05-05

### Changed
- Split the app knowledge documentation system into `app-knowledge-maintenance`, `process-documentation`, and `skill-maintenance` skills with focused responsibilities and progressive-disclosure references.
- Replaced the old `laravel-boost-app-knowledge` and `process-documentation-with-flowcharts` entrypoints with leaner skills for post-change documentation review, process docs and flowcharts, and app skill maintenance.

## 1.0.36 - 2026-05-05

### Changed
- Clarified `laravel-boost-app-knowledge` guidance to avoid monolithic app skills, split skills by module/workflow/integration, evolve skill names and triggers over time, and review affected skills after complex work.

## 1.0.35 - 2026-05-05

### Changed
- Expanded `laravel-boost-app-knowledge` guidance for skill context budgets, progressive disclosure, descriptions, scripts, multi-step workflow checklists, validation loops, and core activation rules.

## 1.0.34 - 2026-05-05

### Added
- Added a `laravel-boost-app-knowledge` skill for creating and maintaining application-specific `.ai/skills` that preserve complex module, workflow, integration, LLM schema, and architecture knowledge for future agents.

## 1.0.33 - 2026-05-04

### Changed
- Replaced the Spatie Laravel Package Tools service provider base class with a native Laravel service provider while preserving config merging and publishing.

## 1.0.32 - 2026-05-03

### Changed
- Updated the `github-issue-briefs` skill so every brief includes a required `Plan` section with single-stage or multi-stage implementation checklists.

## 1.0.31 - 2026-05-03

### Changed
- Reduced the always-loaded Boost core guidelines so generated `AGENTS.md` files stay concise and delegate detailed guidance to skills.
- Replaced the `github-issue-brief-refinement` skill with `github-issue-briefs` and updated core guidance to activate it only for creating or rewriting GitHub issue briefs.

## 1.0.30 - 2026-04-30

### Added
- Added a `laravel-coding-style` skill as the general entry point for preferred Laravel framework coding style, covering lean models, explicit domain methods, relationship traits, after-commit observers, jobs/actions, and lifecycle tests.
- Split Laravel coding style details into focused references for relationship traits, model construction and persistence, model lifecycle events, after-commit observers, workflow jobs/actions, and model lifecycle testing.

### Changed
- Updated core guidelines and Laravel TDD references to activate `laravel-coding-style` for relationship, model lifecycle, observer, and event orchestration conventions.

### Removed
- Removed the standalone `eloquent-relationship-traits` and `model-events-observers-workflows` skill entry points now that their guidance is covered by `laravel-coding-style` references.

## 1.0.29 - 2026-04-30

### Added
- Added a standalone `solid-design` skill for applying SOLID principles to production code and tests, including guidance against monolithic classes, mixed responsibilities, concrete-type branching, fat interfaces, and poor dependency direction.
- Added Laravel TDD integration-fake reference guidance for contract-backed fakes, facade swapping, seeded responses, call assertions, and fake-by-default feature tests.

### Changed
- Updated core coding and testing guidelines to make SOLID a general production-code and test-code standard, with explicit guidance against monolithic unreadable classes.
- Updated Filament testing guidance to prohibit file-local Pest helper functions for setup shortcuts and require inline setup, factory states, scenario builders, `tests/TestCase.php`, or `tests/Support`.
- Folded the standalone `laravel-integration-fakes` skill into `laravel-tdd`.

### Removed
- Removed the standalone `laravel-integration-fakes` skill now that its guidance lives under `laravel-tdd`.

## 1.0.28 - 2026-04-30

### Changed
- Clarified Filament relation manager testing guidance so relation manager behaviour is never tested in page test files and must live in mirrored relation manager test files.
- Updated view and edit page testing references to call out the old relation-manager-in-page-test rule as incorrect for this codebase.

## 1.0.27 - 2026-04-30

### Changed
- Added Filament relation manager testing guidance with mirrored test paths, owner-record scoping, page-context coverage, table interaction, actions, and validation expectations.
- Updated Filament testing guidance to split different test focuses into dedicated `describe()` blocks such as authorization, rendering, table interaction, form submission, validation, header actions, and record actions.
- Adjusted view-page testing guidance so page tests verify relation manager presence while full relation manager behaviour is tested in the mirrored relation manager test file.

## 1.0.26 - 2026-04-30

### Changed
- Added Filament testing references for create and edit pages, including page rendering, authorization, form submission, validation, and header action coverage.
- Updated the Filament testing guide map so agents load create-page and edit-page references for the matching page workflows.

## 1.0.25 - 2026-04-30

### Changed
- Updated the `execution-plans` skill so ExecPlans live only in the GitHub PR body, never local Markdown plan files.
- Simplified ExecPlan structure by combining plan-of-work tracking into `Progress`, folding context and retrospective guidance into `Purpose / Big Picture`, and clarifying `Artifacts and Notes`.
- Updated core delivery workflow guidance to keep PR plans and checklists in the PR body as the source of truth.

## 1.0.24 - 2026-04-30

### Changed
- Rebuilt the Filament Boost skill as a general `filament` skill with focused references for UX, testing, list pages, view pages, authorization/panel access, and supplier/customer portals.
- Added Filament testing guidance requiring page tests to mirror the app's Filament page structure under `tests/Filament`, with dedicated references for list pages, view pages, and authorization/panel access.
- Added Filament UX guidance requiring concise action labels with useful icons, with longer explanatory copy moved to modals, descriptions, helper text, or confirmations.

## 1.0.23 - 2026-04-29

### Changed
- Renamed all bundled Boost skill entry files from `Skill.md` to `SKILL.md` and updated matching skill reference text.

## 1.0.22 - 2026-04-29

### Changed
- Tightened the GitHub issue brief refinement skill so refined briefs use only `Current implementation`, `Target behavior`, `Open questions`, `Scope`, `Risks`, `Affected areas`, and `Key tests`, with overlapping acceptance criteria, desired outcome, and edge-case content condensed into those sections.

## 1.0.21 - 2026-04-28

### Changed
- Clarified queued job guidance so dispatched job constructors are data-only payload assignment and all executable work, collaborator resolution, guards, and errors happen inside `handle(...)`.

### Removed
- Removed the bundled `laravel-actions` Boost skill because it now lives in its own Composer package.

## 1.0.20 - 2026-04-25

### Changed
- Added GPT-5.5-oriented agent behavior guidance to the shared Boost prompt, emphasizing outcome-first work, scoped process use, evidence checks, validation, and clear stopping conditions.
- Updated the README to describe GPT-5.5-aligned prompt behavior for Laravel Boost consumers.

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
- Renamed Boost skill files within dedicated skill directories for improved naming consistency.
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
