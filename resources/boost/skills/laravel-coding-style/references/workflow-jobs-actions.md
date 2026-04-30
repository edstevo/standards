# Workflow Jobs And Actions

Load this reference when deciding where follow-up workflow work belongs.

## Layering

- Models/traits own state transitions, invariants, and explicit event firing.
- Observers coordinate reactions by dispatching jobs/events or calling methods that trigger further explicit events.
- Jobs/actions perform heavy work, retries, integration IO, and long-running workflow steps.

## Jobs

Use native Laravel jobs for work that is queued, delayed, retryable, slow, or integration-heavy.

In observer-driven workflows:
- observers dispatch the job boundary
- jobs do the work
- jobs resolve collaborators inside `handle(...)`
- external integration IO happens in jobs, not observers

Do not constructor-inject services into queued jobs. A dispatched job constructor runs in the dispatching process; treat it as data-only payload assignment.

## Actions

Use actions for reusable application behaviour and domain workflow steps.

If a job needs shared application logic, the job should call an action rather than absorb that logic itself.

Prefer action names that describe the task:
- `RaiseWarehouseOrderForFulfillmentOrder`
- `RaisePurchaseOrderForFulfillmentOrder`
- `SubmitPurchaseOrder`
- `ScheduleCollection`
- `SyncFulfillmentToErp`

## External IO

If the follow-up:
- can be slow
- can fail transiently
- needs retry/backoff
- talks to Shopify, ERP, couriers, payment providers, or any third-party system

then dispatch a job from the observer instead of calling the integration directly.
