# State Pattern

Load this overview when a Laravel model has lifecycle states, workflow transitions, or scattered state checks. Keep this file small, then load only the focused reference that matches the work.

For complex model workflows, standardize on `spatie/laravel-model-states` instead of building a custom transition framework.

## Default Rule

Simple lifecycle changes can stay as named model methods, following `model-lifecycle-events.md`.

Use Spatie model states when:
- workflow complexity grows
- valid transitions matter
- actions depend on lifecycle stage
- rules differ between states
- a model has more than one state dimension
- state checks appear across controllers, services, jobs, observers, or policies

Do not use state classes for simple flags or basic two-state values. A `User` with `active` and `inactive` is usually a boolean or enum.

## Standard Shape

- Add `spatie/laravel-model-states` to the application when complex model states are introduced.
- Models with Spatie state fields use `Spatie\ModelStates\HasStates` and implement `Spatie\ModelStates\HasStatesContract`.
- Each state column is a string column cast to an abstract state class.
- Each abstract state class extends `Spatie\ModelStates\State` and configures defaults and valid transitions.
- Use explicit `allowTransition(...)` or `allowTransitions(...)`; avoid `allowAllTransitions()` unless every transition is genuinely valid.
- Use custom Spatie transition classes when a transition needs contextual data or extra model mutations.
- Keep expressive model methods as the public API.

Good:

```php
$invoice->markAsPaid(
    paidAt: $payment->received_at,
);
```

Avoid application callers guessing state classes:

```php
$invoice->state->transitionTo(Paid::class);
```

The model method may call Spatie's `transitionTo(...)` internally.

## Load Map

| Topic | Reference | Load When |
|-------|-----------|-----------|
| State classes | `state-pattern-state-classes.md` | Adding Spatie state casts, abstract state classes, concrete state classes, defaults, or valid transition config |
| Transition mechanics | `state-pattern-transitions.md` | Adding expressive model methods, Spatie transition classes, before/after model events, or transition persistence rules |
| Multiple state dimensions | `state-pattern-multiple-states.md` | A model has independent lifecycle, approval, despatch, delivery, payment, or other state dimensions |
| Transition context | `state-pattern-context.md` | Passing contextual data such as paid timestamps, delivery timestamps, cancellation reasons, or supplier references |
| Events and policies | `state-pattern-events-and-policies.md` | Deciding between before/after model events, Spatie `StateChanged`, domain events, observers, and Laravel Policies |
| Testing | `state-pattern-testing.md` | Testing valid transitions, invalid transitions, context application, model events, Spatie `StateChanged`, or multi-state models |

## Boundary

- Expressive model methods such as `approve()`, `markAsPaid()`, or `markAsDespatched()` are the public API.
- Spatie state config answers which transitions are valid.
- Spatie transition classes perform contextual state changes.
- Before model events validate or halt before the transition is attempted.
- After model events and Spatie `StateChanged` listeners react after persistence.
- Laravel Policies answer whether the user is allowed to attempt the action.
