---
name: solid-design
description: Apply SOLID design principles to general production code and test code. Use when implementing or refactoring code that risks monolithic classes, mixed responsibilities, unreadable workflows, concrete-type branching, unclear contracts, non-substitutable fakes, fat interfaces, or poor dependency direction.
license: MIT
metadata:
  domain: architecture
  role: specialist
  scope: implementation
  triggers: SOLID, SRP, OCP, LSP, ISP, DIP, single responsibility, open closed, Liskov, interface segregation, dependency inversion, monolithic class, god class, unreadable code, refactor
---

# SOLID Design

Use SOLID as practical design guidance for production code and tests:
- make behaviour easier to understand
- avoid monolithic classes and unreadable workflows
- make tests easier to write without broad mocks or hidden helpers
- make new variants possible through extension rather than repeated edits
- keep contracts small and implementations substitutable

Do not add abstractions for ceremony. Add them when they remove a real reason to change, make a boundary explicit, or let a new implementation fit without changing the caller.

Use this skill whenever you:
- add or refactor non-trivial production code
- split a large class, action, service, job, controller, resource, or test file
- see concrete-type branching, `instanceof`, or class-name switches in domain code
- design contracts, repositories, integrations, fakes, strategies, or workflow collaborators
- review code for readability, responsibility boundaries, testability, or dependency direction

## Single Responsibility Principle

A class should have one reason to change. A test should prove one behavioural concern.

Prefer:
- authorization in policies, gates, middleware, or the caller that owns access
- persistence details in repositories, models, query objects, or dedicated actions
- formatting and presentation in resources, serializers, views, transformers, or output classes
- workflow orchestration in actions, services, jobs, observers, or model transition methods according to local conventions
- one test per meaningful behaviour, with grouped setup only where the group shares that behaviour

Avoid:
- one class that checks authorization, queries storage, runs domain rules, formats output, and calls integrations
- one action or job that absorbs reusable business logic and infrastructure details
- catch-all services, managers, processors, or resources with vague names and many unrelated responsibilities
- one test that proves state transition, accounting records, emails, integration calls, and audit timeline in one body
- helper functions that hide the domain story instead of giving repeated setup a real support class, factory state, or builder

## Open/Closed Principle

Code should be open for extension and closed for repeated modification.

When behaviour varies by type, provider, payment method, output format, route, carrier, or policy case, prefer a small contract and focused implementations over type checks.

Prefer:
- adding a new implementation behind an interface
- dispatching to a strategy, action, policy, event listener, or value object
- adding a new enum case only when the switch is at a genuine boundary and each case still delegates to focused behaviour

Avoid:
- adding another `if ($thing instanceof ...)` branch every time a new type appears
- growing a central `switch` that knows the details of every provider or workflow variant
- modifying a stable caller whenever a new concrete implementation is introduced

## Liskov Substitution Principle

Any implementation of an abstraction should be usable anywhere that abstraction is accepted.

Keep implementations aligned on:
- return types and shaped arrays/DTOs
- exception behaviour and failure semantics
- required preconditions
- side effects that callers rely on
- fake behaviour versus real integration behaviour

If a contract says `getAll()` returns an array, every implementation should return the same shape. If a facade fake replaces a real integration, tests should be able to call the same methods and receive the same kind of response or exception.

Avoid:
- a fake that accepts data the real implementation would reject
- a subclass that throws for valid parent inputs because it has stricter preconditions
- repository implementations that return different container types and force consumers to branch
- consumers that need `is_array`, `instanceof Collection`, or concrete-class checks after calling a contract

## Interface Segregation Principle

Clients should not depend on methods they do not use.

Prefer small role-based interfaces, even when that means an interface has one method. A caller should depend only on the capability it needs.

Prefer:
- `CanSendShipmentUpdates`
- `ProvidesReminderEmail`
- `CalculatesTax`
- `AcceptsPayment`
- `RecordsCourierDispatches`

Avoid:
- fat repository, service, or integration contracts that every implementation must fully satisfy
- null returns, empty methods, or "not supported" exceptions added only because an interface is too broad
- forcing a test fake to implement unrelated methods just to satisfy a wide contract

## Dependency Inversion Principle

High-level workflow and domain code should depend on abstractions, not low-level details.

Prefer:
- constructor or method injection of contracts for external boundaries
- Laravel service-provider bindings from contracts to concrete implementations
- facades backed by contracts when that gives tests a native `fake()` API
- contracts owned by the high-level behaviour's need, not by vendor SDK details

Avoid:
- domain actions creating vendor clients, SDK classes, HTTP clients, filesystem drivers, or concrete repositories directly
- high-level workflow code depending on Eloquent, storage, or transport details when it only needs a small capability
- abstractions that leak vendor SDK types into the domain contract

Dependency injection is the mechanism. Dependency inversion is the design direction: both high-level code and low-level implementations depend on the abstraction.

## General Coding Implications

Code should be readable by following the names and main control flow.

Prefer:
- small classes with domain-specific names
- short orchestration methods that delegate distinct responsibilities to focused collaborators
- explicit dependencies, typed contracts, and clear return shapes
- boring, readable code over clever compression
- extracting a new class when a private method starts to describe a separate responsibility

Avoid:
- monolithic classes that require scrolling through unrelated behaviours
- long methods that mix decision-making, persistence, formatting, integration calls, and side effects
- vague names such as `Manager`, `Processor`, or `Service` when they hide what the class actually owns
- dense fluent chains or clever abstractions that make the business flow harder to audit

## Testing Implications

SOLID should be visible in the tests:
- test names describe behaviour, not implementation calls
- each test has one reason to fail
- tests use real domain flows where possible and fake only external boundaries
- fakes obey the same contracts and response shapes as real implementations
- setup stays visible when it explains the behaviour
- repeated setup becomes factory states, scenario builders, `tests/TestCase.php`, or `tests/Support`, not file-local helper functions
- broad tests are split by responsibility instead of adding assertion helpers that hide several behaviours

When a test is awkward, check the design before adding test-only shortcuts:
- too much setup may mean the production class owns too many responsibilities
- repeated concrete-type checks may mean a missing interface or strategy
- a fake that cannot be written cleanly may mean the integration boundary is not explicit
- different implementations needing different assertions may mean the contract is not substitutable
- a support helper with many flags may mean the scenario builder or factory states need clearer domain language

## Design Smells To Act On

Treat these as prompts to refactor:
- class has multiple unrelated reasons to change
- new variants require editing the same central conditional
- consumer branches based on concrete class after accepting an interface
- implementation returns a different type or shape than the contract implies
- interface forces no-op, null, or unsupported methods
- domain code constructs low-level infrastructure directly
- tests need broad mocks, hidden helper functions, or mixed assertions to cover one workflow

The desired end state is simple: callers know the capability they need, implementations provide that capability consistently, and tests can prove one behaviour without compensating for poor boundaries.
