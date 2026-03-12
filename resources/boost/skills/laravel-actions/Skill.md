---
name: laravel-actions
description: Use the Laravel Actions package for Laravel action classes. Apply when creating or refactoring action classes, standardising on one-class-one-task classes in app/Actions, calling actions via ::run(), or testing and mocking actions cleanly. Keep queued or asynchronous classes on native Laravel Jobs and Queues instead of modelling jobs as Laravel Actions by default.
license: MIT
metadata:
  domain: backend
  role: specialist
  scope: implementation
  triggers: Laravel Actions, AsAction, action classes, ::run(), mock actions, spy actions, app/Actions
---

# Laravel Actions

Use `lorisleiva/laravel-actions` for application action classes.

Default posture:
- one class, one task
- actions live in `app/Actions` or domain `Actions/` folders
- business logic lives in `handle(...)`
- collaborators are constructor-injected so the action stays container-resolvable
- application code calls actions via `MyAction::run(...)`
- queued and asynchronous classes stay on native Laravel Jobs and Queues
- if queued work needs shared business logic, the job should call the action

Use this skill when you:
- add or refactor an action class
- replace a generic service or invokable class with a clearer one-task action
- want consistent `app/Actions` structure and verb-led naming
- need package-native mocking or spying for action collaborators
- need to decide whether a class should be an action or a queued job

## Core Workflow

1. Decide whether the class is an action or a job.
2. Create a verb-led action under `app/Actions/...` and add `AsAction`.
3. Put the core behaviour in `handle(...)`; keep the class container-resolvable.
4. Call the action via `::run(...)` from controllers, services, listeners, commands, or jobs.
5. Test the real action directly when it is the subject under test.
6. Mock or spy the action only when another class depends on it.
7. For queued work, create a native Laravel Job and let that job call the action if needed.

## Reference Guide

References are load-on-demand support files in this skill's `references/` directory.

Use them deliberately:
- stay in this main skill for the default rules and package boundary
- load a reference when the task matches its topic
- load the reference before planning or editing if that topic is central to the task
- do not load every reference automatically; load only the ones that fit

Why references exist:
- they keep `Skill.md` short and readable
- they hold deeper package guidance that would otherwise bloat the main skill
- they let you pull in precise instructions only when the task needs them

Load detailed guidance based on context:

| Topic | Reference | Load When |
|-------|-----------|-----------|
| One class, one task | `references/one-class-one-task.md` | Creating or refactoring an action, deciding class shape, placing actions under `app/Actions`, or choosing between `handle()` / `::run()` / container resolution |
| Mocking and testing actions | `references/mock-and-test.md` | Writing tests around actions, isolating an action collaborator, choosing between real execution and mocks/spies, or using `shouldRun()` / `allowToRun()` / `clearFake()` |

## Action vs Job

Use a Laravel Action when:
- the class is application business logic executed inside the current flow
- the class should represent one focused task with a clean `::run(...)` call site
- package-native mocking or spying will improve tests

Use a native Laravel Job when:
- the class itself represents queued, asynchronous, delayed, or after-response work
- retries, backoff, queue routing, Horizon visibility, or queue middleware are central concerns
- the class should be treated first as transport/execution infrastructure

Preferred boundary:
- `Controller/Service/Listener -> Action::run(...)`
- `Job::handle() -> Action::run(...)`

Do not default to modelling queued classes as Laravel Actions just because the package supports dispatching jobs.

## Package Defaults

- Prefer `use Lorisleiva\Actions\Concerns\AsAction;`
- Prefer `handle(...)` as the core business method
- Keep actions container-resolvable so constructor injection and package fakes work
- Prefer `::run(...)` over manual instantiation at call sites
- Name actions as explicit verb-led tasks
- Keep action classes small and single-purpose

```php
<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Services\Pricing\PriceCalculator;
use Lorisleiva\Actions\Concerns\AsAction;

class RecalculateOrderTotals
{
    use AsAction;

    public function __construct(
        protected PriceCalculator $priceCalculator,
    ) {}

    public function handle(Order $order): void
    {
        $order->total_gross = $this->priceCalculator->grossFor($order);
        $order->save();
    }
}
```

```php
RecalculateOrderTotals::run($order);
```

## Testing Defaults

- If the action itself is the subject under test, run the real action and assert outcomes.
- If another class depends on the action, use Laravel Actions mocking or spying helpers instead of ad-hoc container hacks.
- Prefer `shouldRun()` / `shouldNotRun()` when the test only cares whether the action was invoked.
- Prefer `allowToRun()` / `spy()` when the test runs first and asserts afterward.
- Use `partialMock()` rarely; if you need it often, the action is likely doing too much.
- Reset action fakes with `clearFake()` when fake state could leak across tests.

If the task is mainly about package semantics and class shape, load `references/one-class-one-task.md`.
If the task is mainly about mocking and tests, load `references/mock-and-test.md`.
