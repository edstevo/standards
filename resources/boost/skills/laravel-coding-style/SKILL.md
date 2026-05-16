---
name: laravel-coding-style
description: "Apply the preferred Laravel coding style for this codebase. Use when writing or refactoring Laravel models, relationships, observers, lifecycle workflows, jobs, actions, persistence flows, and model-event tests. Guides lean Eloquent models, explicit domain methods, reusable relationship traits, the observer pattern, native jobs for queued work, and synchronous Laravel Actions."
license: MIT
metadata:
  domain: laravel
  role: specialist
  scope: implementation
  triggers: Laravel, Eloquent, models, relationships, relationship traits, observers, model events, lifecycle events, workflows, state transitions, saveQuietly, fireModelEvent, after commit
---

# Laravel Coding Style

This skill describes my preferred Laravel framework coding style. Use it alongside `solid-design` for general design boundaries and `laravel-tdd` for test-writing workflow.

The default shape is:
- Eloquent models stay lean and expose expressive domain methods.
- Reusable Eloquent relationships live in small relationship traits.
- Non-trivial persistence flows use explicit property assignment and `associate()` instead of large opaque arrays.
- Builder-style APIs are the default when object, DTO, model graph, command, report, import, filtering, or workflow construction becomes complex, option-heavy, multi-step, or hard to read at the call site. Do not wait for repeated usage before introducing a builder.
- Lifecycle changes happen through named model transition methods.
- Transition methods guard, mutate, `saveQuietly()`, then fire one explicit model event.
- Observers run after commit and stay thin: identify the lifecycle event, then delegate follow-up work to native Laravel jobs/events, actions, services, or model methods that trigger further explicit events.
- Native Laravel jobs perform queued, delayed, retryable, asynchronous, integration-heavy, and long-running workflow steps. Jobs that always need committed data should implement `ShouldQueueAfterCommit` instead of relying on `->afterCommit()` dispatch chains.
- Laravel Actions perform synchronous, reusable application behaviour. Do not use `lorisleiva/laravel-actions` action classes as queued jobs; create a native job and have the job call the action when shared logic is needed.

Use this skill whenever you:
- add or refactor Eloquent models or relationships
- create models that share common relationships
- split bulky models into composable concerns
- introduce model lifecycle events or state transitions
- orchestrate multi-step workflows such as created -> route -> dispatch
- trigger timeline/audit, integration sync, queued work, or next-step workflow side effects from model changes
- test model lifecycle events, observers, or event-driven workflows

## Reference Guide

References are load-on-demand support files in this skill's `references/` directory.

Load detailed guidance based on the task:

| Topic | Reference | Load When |
|-------|-----------|-----------|
| Eloquent relationship traits | `references/eloquent-relationship-traits.md` | Adding/modifying Eloquent relationships, creating shared relationship traits, refactoring bulky models into relationship concerns, or needing relationship docblocks/return types |
| Builder pattern | `references/builder-pattern.md` | Construction has many optional values or steps, large constructors, arrays used as informal configuration objects, unclear action payloads, command/report/import/filter setup, repeated setup logic, or complex test setup that is not just model factory data |
| Model construction and persistence | `references/model-construction-persistence.md` | Creating non-trivial model graphs, deciding between explicit assignment and mass assignment, using `associate()`, or wrapping related writes in transactions |
| Model lifecycle events | `references/model-lifecycle-events.md` | Adding model transition methods, custom observable events, `saveQuietly()`, `fireModelEvent(...)`, or behaviour traits for state changes |
| Observer pattern | `references/observer-pattern.md` | Writing observers, keeping observer methods thin, delegating follow-up work, using after-commit handling, or avoiding hidden workflow/domain logic in observers |
| Workflow jobs and actions | `references/workflow-jobs-actions.md` | Deciding whether follow-up work belongs in observers, jobs, actions, or model methods |
| Testing model lifecycle workflows | `references/testing-model-lifecycle.md` | Testing model transitions, explicit model events, observer follow-up behaviour, or after-commit workflow boundaries |

## General Rules

- Keep model APIs domain-readable. Prefer `$order->markAsSubmitted()` over scattered state mutation.
- Keep relationship structure consistent. If a relationship can be reused, prefer a dedicated trait under `App\Models\Relationships`.
- Keep non-trivial persistence flows explicit enough that the graph and relationships are readable.
- Default to focused builders when construction is difficult to read, even before repetition appears; keep builder methods intention-revealing and domain-specific, with explicit terminal methods such as `build()`, `make()`, `create()`, `toDto()`, `toArray()`, or `save()` that accurately communicate side effects.
- Keep observers as a predictable reaction layer, not a place for domain decisions, private helper methods, workflow orchestration, or integration IO.
- Use observers only for global lifecycle consequences; call actions directly when behaviour belongs to one specific workflow, command, import, checkout, or admin path.
- Keep queued, delayed, retryable, asynchronous, slow, and integration-heavy work in native Laravel jobs.
- Keep Laravel Actions for synchronous reusable business steps; jobs may call actions, but actions are not the queue boundary.
- Keep tests aligned with the same boundaries: scenario-focused model transition files prove one transition; observer or workflow files prove follow-up orchestration separately.

## Checklist

- [ ] Relationship conventions are handled through `references/eloquent-relationship-traits.md` when relationships are involved.
- [ ] Builder guidance is loaded as soon as construction is complex, option-heavy, multi-step, difficult to read, or currently expressed through large constructors/arrays.
- [ ] Persistence, lifecycle, observer, job/action boundary, and lifecycle-test references are loaded only when those topics are involved.
- [ ] Models expose expressive methods instead of leaking state mutation across callers.
- [ ] Observers dispatch or delegate work after commit and do not call external integrations directly.
- [ ] Observer methods are thin, flat, and free of private helper methods or hidden business rules.
- [ ] Jobs that need committed data use `ShouldQueueAfterCommit` when the job can own that guarantee.
- [ ] Queued or asynchronous work uses native Laravel jobs, not `lorisleiva/laravel-actions` dispatch helpers.
- [ ] Jobs call actions only to reuse synchronous business logic.
- [ ] Tests cover lifecycle behaviour in focused scenario files and layers instead of one broad workflow assertion.
