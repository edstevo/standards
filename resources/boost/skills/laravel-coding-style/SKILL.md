---
name: laravel-coding-style
description: "Apply the preferred Laravel coding style for this codebase. Use when writing or refactoring Laravel migrations, models, relationships, DTOs, factories, managers, specifications, observers, lifecycle workflows, state pattern workflows, strategy pattern implementations, jobs, actions, persistence flows, and model-event tests. Guides safe schema identifiers, lean Eloquent models, explicit domain methods, reusable relationship traits, typed data contracts, centralized construction and implementation selection, reusable business rule checks, state transitions, interchangeable strategy classes, thin observers, native Laravel jobs for queued/Horizon work, and synchronous Laravel Actions."
license: MIT
metadata:
  domain: laravel
  role: specialist
  scope: implementation
  triggers: Laravel, migrations, schema, indexes, constraints, Eloquent, models, relationships, relationship traits, DTO, Data objects, spatie/laravel-data, typed payloads, factory pattern, factories, manager pattern, managers, specification pattern, specifications, eligibility rules, business rule checks, drivers, providers, connections, adapters, observers, model events, lifecycle events, workflows, action pattern, Laravel Actions, AsAction, state transitions, strategy pattern, strategies, resolvers, interchangeable algorithms, saveQuietly, fireModelEvent, after commit
---

# Laravel Coding Style

This skill describes my preferred Laravel framework coding style. Use it alongside `solid-design` for general design boundaries and `laravel-tdd` for test-writing workflow.

Treat this file as a routing map. Keep only the relevant reference files in context for the work being done.

## Decision Map

| Topic | Reference | Load When |
|-------|-----------|-----------|
| Database migrations | `references/database-migrations.md` | Adding or changing indexes, unique constraints, or foreign keys, especially composite or long identifiers |
| Eloquent model API | `references/model-lifecycle-events.md` | Adding named lifecycle methods, custom observable events, `saveQuietly()`, or `fireModelEvent(...)` |
| Relationships | `references/eloquent-relationship-traits.md` | Adding Eloquent relationships, shared relationship traits, relationship docblocks, or relationship return types |
| Persistence | `references/model-construction-persistence.md` | Creating non-trivial model graphs, choosing explicit assignment vs mass assignment, using `associate()`, or wrapping related writes in transactions |
| DTO | `references/dto-pattern.md` | Replacing raw arrays or designing typed request, action, job, integration, import/export, or AI-readable payloads |
| Builder | `references/builder-pattern.md` | Construction is option-heavy, multi-step, hard to scan, or currently expressed through large constructors or arrays |
| Action | `references/action-pattern.md` | Creating or refactoring one synchronous business operation using `AsAction` and `::run(...)` |
| Job/action boundary | `references/workflow-jobs-actions.md` | Deciding whether work is synchronous action logic or asynchronous Laravel queue/Horizon work |
| Observer | `references/observer-pattern.md` | Reacting to Eloquent lifecycle events, delegating after commit, or removing hidden observer workflow logic |
| State | `references/state-pattern.md` | Replacing scattered lifecycle checks with expressive model methods and Spatie model states |
| Strategy | `references/strategy-pattern.md` | Selecting between interchangeable algorithms such as pricing, VAT, shipping, routing, or provider-specific flows |
| Specification | `references/specification-pattern.md` | Extracting reusable side-effect-free boolean business rules or eligibility checks |
| Policy vs state/specification | `references/state-pattern-events-and-policies.md` | Separating actor authorization from lifecycle validity and reusable domain eligibility rules |
| Factory/manager | `references/factory-manager-pattern.md` | Centralizing construction or selecting drivers, providers, connections, adapters, strategies, or specification implementations |
| Lifecycle tests | `references/testing-model-lifecycle.md` | Testing model transitions, observer follow-up behaviour, explicit model events, or after-commit boundaries |

## Pattern Boundaries

- Models expose domain-readable APIs. Simple lifecycle changes can stay as named model methods; complex workflows use `spatie/laravel-model-states`.
- DTOs carry typed data. They do not perform business workflows.
- Actions perform synchronous business operations through `lorisleiva/laravel-actions`, `AsAction`, and `::run(...)`.
- Jobs are native Laravel asynchronous queue units for Horizon. Jobs use `::dispatch(...)`, pass Eloquent models when the model is the payload, and avoid a `Job` suffix.
- Observers are shallow global lifecycle reactions. They apply technical guards and delegate; detailed observer rules live in `observer-pattern.md`.
- Specifications answer reusable boolean domain-rule questions. Policies authorize actors. Strategies execute interchangeable algorithms. States own lifecycle behaviour.
- Factories construct implementations. Managers select named drivers, providers, connections, tenants, or implementations. Builders assemble complex objects or payloads.

## Workflow Shape

Prefer this flow when the pieces are relevant:

```text
Controller / command / listener / UI handler
-> Request validation
-> DTO
-> Action
-> Specification, state, strategy, manager, or factory as needed
-> Model persistence and explicit events
-> Observer, event listener, or native job for global follow-up work
```

## Checklist

- [ ] Load the smallest reference set that matches the work.
- [ ] Give composite and potentially long schema identifiers explicit names within the deployed database's limit.
- [ ] Keep the chosen pattern's responsibility narrow and named in domain language.
- [ ] Do not duplicate business rules across controllers, observers, jobs, actions, models, or tests.
- [ ] Keep synchronous work in actions and asynchronous work in native Laravel jobs.
- [ ] Keep observer methods shallow and move workflow logic to the owning pattern.
- [ ] Add focused tests at the same boundary as the behaviour being changed.
