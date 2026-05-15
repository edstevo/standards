# Workflow Jobs And Actions

Load this reference when deciding where follow-up workflow work belongs.

## Layering

- Models/traits own state transitions, invariants, and explicit event firing.
- Observers detect lifecycle events and delegate reactions by dispatching native jobs/events or calling methods that trigger further explicit events.
- Native Laravel jobs perform queued, delayed, retryable, asynchronous, integration-heavy, and long-running workflow steps.
- Laravel Actions perform synchronous, reusable application behaviour and may be called from jobs.

## Queue Boundary

Use native Laravel job classes for follow-up work that will be queued, delayed, retried, or run asynchronously. When an observer starts that work, it should dispatch a native job/event, never an action class.

Do not use `lorisleiva/laravel-actions` action classes as queued jobs, even though the package exposes job-style dispatch helpers such as `::dispatch(...)`. Actions are the synchronous business-logic boundary in this codebase; jobs are the queue boundary.

If asynchronous work needs shared action logic:
- create a native job under `app/Jobs` or the project's domain-local jobs directory
- pass only serializable payload data to the job constructor
- resolve collaborators and call the action inside `handle(...)`
- make the job own queue contracts, retries, middleware, backoff, uniqueness, and after-commit semantics

Bad:

```php
ReconcileImplementedSalesOrderAdjustment::dispatch($adjustment);
```

Good:

```php
ReconcileImplementedSalesOrderAdjustmentJob::dispatch($adjustment->id);
```

## Jobs

Use native Laravel jobs for work that is queued, delayed, retryable, slow, or integration-heavy.

In observer-driven workflows:
- observers dispatch the job boundary without absorbing workflow logic
- jobs do the work
- jobs resolve collaborators inside `handle(...)`
- external integration IO happens in jobs, not observers

Do not constructor-inject services into queued jobs. A dispatched job constructor runs in the dispatching process; treat it as data-only payload assignment.

When a queued job should never run until surrounding database transactions commit, implement `Illuminate\Contracts\Queue\ShouldQueueAfterCommit` on the job class. Prefer that class-level contract over repeating dispatch-site chains like `SomeJob::dispatch($id)->afterCommit();`.

Use dispatch-level `->afterCommit()` only when the after-commit requirement is genuinely local to one dispatch path, or when the job cannot implement the interface in the current Laravel version.

```php
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class DetachUserFromAccountRoleContacts implements ShouldQueueAfterCommit
{
    public function __construct(
        public readonly int $userId,
    ) {}

    public function handle(): void
    {
        // Resolve collaborators and perform the work here.
    }
}
```

When the reusable business logic belongs in an action, keep the queue concerns on the job:

```php
use App\Actions\SalesOrderAdjustment\ReconcileImplementedSalesOrderAdjustment;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class ReconcileImplementedSalesOrderAdjustmentJob implements ShouldQueueAfterCommit
{
    public function __construct(
        public readonly int $adjustmentId,
    ) {}

    public function handle(): void
    {
        ReconcileImplementedSalesOrderAdjustment::run($this->adjustmentId);
    }
}
```

## Actions

Use actions for reusable, synchronous application behaviour and domain workflow steps.

Call actions directly via `::run(...)`, or from controllers, services, commands, jobs, and tests when the work should happen in the current process.

If a job needs shared application logic, the job should call an action rather than absorb that logic itself.

Do not add queue contracts, job middleware, retry/backoff configuration, or queue-specific behaviour to action classes in this codebase. Put that behaviour on the native job that calls the action.

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
