---
name: laravel-coding-style
description: "Apply the preferred Laravel coding style for this codebase. Use when writing or refactoring Laravel models, relationships, observers, lifecycle workflows, jobs, actions, persistence flows, and model-event tests. Guides lean Eloquent models, explicit domain methods, reusable relationship traits, after-commit observers, and jobs/actions for follow-up work."
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
- Lifecycle changes happen through named model transition methods.
- Transition methods guard, mutate, `saveQuietly()`, then fire one explicit model event.
- Observers run after commit and orchestrate follow-up work by dispatching jobs/events or calling methods that trigger further explicit events.
- Jobs/actions perform heavy work, retries, integration IO, and long-running workflow steps.

Use this skill whenever you:
- add or refactor Eloquent models or relationships
- create models that share common relationships
- split bulky models into composable concerns
- introduce model lifecycle events or state transitions
- orchestrate multi-step workflows such as created -> route -> dispatch
- trigger timeline/audit, integration sync, or next-step workflow side effects from model changes
- test model lifecycle events, observers, or event-driven workflows

## Reference Guide

References are load-on-demand support files in this skill's `references/` directory.

Load detailed guidance based on the task:

| Topic | Reference | Load When |
|-------|-----------|-----------|
| Eloquent relationship traits | `references/eloquent-relationship-traits.md` | Adding/modifying Eloquent relationships, creating shared relationship traits, refactoring bulky models into relationship concerns, or needing relationship docblocks/return types |
| Model construction and persistence | `references/model-construction-persistence.md` | Creating non-trivial model graphs, deciding between explicit assignment and mass assignment, using `associate()`, or wrapping related writes in transactions |
| Model lifecycle events | `references/model-lifecycle-events.md` | Adding model transition methods, custom observable events, `saveQuietly()`, `fireModelEvent(...)`, or behaviour traits for state changes |
| After-commit observers | `references/after-commit-observers.md` | Writing observers, guarding observer reactions, dispatching timeline/integration/next-step work, or avoiding direct integration IO in observers |
| Workflow jobs and actions | `references/workflow-jobs-actions.md` | Deciding whether follow-up work belongs in observers, jobs, actions, or model methods |
| Testing model lifecycle workflows | `references/testing-model-lifecycle.md` | Testing model transitions, explicit model events, observer follow-up behaviour, or after-commit workflow boundaries |

## General Rules

- Keep model APIs domain-readable. Prefer `$order->markAsSubmitted()` over scattered state mutation.
- Keep relationship structure consistent. If a relationship can be reused, prefer a dedicated trait under `App\Models\Relationships`.
- Keep non-trivial persistence flows explicit enough that the graph and relationships are readable.
- Keep observers as a reaction layer, not a place for domain decisions or integration IO.
- Keep jobs/actions responsible for work that can be slow, retryable, or reused.
- Keep tests aligned with the same boundaries: scenario-focused model transition files prove one transition; observer or workflow files prove follow-up orchestration separately.

## Checklist

- [ ] Relationship conventions are handled through `references/eloquent-relationship-traits.md` when relationships are involved.
- [ ] Persistence, lifecycle, observer, jobs/actions, and lifecycle-test references are loaded only when those topics are involved.
- [ ] Models expose expressive methods instead of leaking state mutation across callers.
- [ ] Observers dispatch work after commit and do not call external integrations directly.
- [ ] Jobs/actions own heavy work and external IO.
- [ ] Tests cover lifecycle behaviour in focused scenario files and layers instead of one broad workflow assertion.
