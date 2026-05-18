# Workflow Jobs And Actions

Load this reference when deciding where follow-up workflow work belongs.

## Layering

- Models/traits own state transitions, invariants, and explicit event firing.
- Specifications own reusable side-effect-free business rule checks and eligibility decisions.
- Observers detect lifecycle events and delegate reactions by dispatching native jobs/events or calling methods that trigger further explicit events.
- Native Laravel jobs are asynchronous queued work units pushed onto the Laravel queue and operated by Laravel Horizon for delayed, retryable, integration-heavy, and long-running workflow steps.
- Laravel Actions perform synchronous, reusable application behaviour through `AsAction` classes called with `::run(...)`; they may be called from jobs.

## Queue Boundary

Use native Laravel job classes for follow-up work that will be queued, delayed, retried, or run asynchronously. When an observer starts that work, it should dispatch a native job/event, never an action class.

Do not use `lorisleiva/laravel-actions` action classes as queued jobs, even though the package exposes job-style dispatch helpers such as `::dispatch(...)`. The Laravel Actions package is only for synchronous action classes in this codebase. Native Laravel jobs are the asynchronous queue/Horizon boundary.

`::dispatch(...)` should mean Laravel framework dispatch from a native job/event class:

```php
// Native Laravel job, not an AsAction class.
SubmitQuote::dispatch($quote);
```

In that example, `SubmitQuote` is a native Laravel job, not a class using `AsAction`.

If asynchronous work needs shared action logic:
- create a native job under `app/Jobs` or the project's domain-local jobs directory
- pass Eloquent models directly when the model is the payload; let Laravel serialize queued models
- keep job constructors to payload assignment only; do not inject services
- resolve collaborators and call the action inside `handle(...)`
- make the job own queue contracts, retries, middleware, backoff, uniqueness, and after-commit semantics

Bad:

```php
\App\Actions\SalesOrderAdjustment\ReconcileImplementedSalesOrderAdjustment::dispatch($adjustment);
```

Good:

```php
\App\Jobs\SalesOrderAdjustment\ReconcileImplementedSalesOrderAdjustment::dispatch($adjustment);
```

## Jobs

Use native Laravel jobs for work that is queued, delayed, retryable, slow, integration-heavy, or intended to run through Horizon.

In observer-driven workflows:
- observers dispatch the job boundary without absorbing workflow logic
- jobs do the work
- jobs resolve collaborators inside `handle(...)`
- external integration IO happens in jobs, not observers

Do not constructor-inject services into queued jobs. A dispatched job constructor runs in the dispatching process; treat it as payload assignment only.

Pass the model itself when the job operates on an Eloquent model. Laravel serializes queued models; do not pass model IDs and re-query purely for queue serialization.

When a queued job should never run until surrounding database transactions commit, implement `Illuminate\Contracts\Queue\ShouldQueueAfterCommit` on the job class. Prefer that class-level contract over repeating dispatch-site chains like `SyncAccount::dispatch($account)->afterCommit();`.

Use dispatch-level `->afterCommit()` only when the after-commit requirement is genuinely local to one dispatch path, or when the job cannot implement the interface in the current Laravel version.

Do not suffix job class names with `Job`. The namespace and queue contract already identify the class as a job. Prefer operation names:
- `SubmitQuote`
- `SyncFulfillmentToErp`
- `RecordInvoiceTimelineEntry`
- `ReconcileImplementedSalesOrderAdjustment`

Avoid:
- `SubmitQuoteJob`
- `SyncFulfillmentToErpJob`
- `RecordInvoiceTimelineEntryJob`

```php
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class DetachUserFromAccountRoleContacts implements ShouldQueueAfterCommit
{
    public function __construct(
        public readonly User $user,
    ) {}

    public function handle(): void
    {
        // Resolve collaborators and perform the work here.
    }
}
```

When the reusable business logic belongs in an action, keep the queue concerns on the job:

```php
use App\Actions\SalesOrderAdjustment\ReconcileImplementedSalesOrderAdjustment as ReconcileImplementedSalesOrderAdjustmentAction;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class ReconcileImplementedSalesOrderAdjustment implements ShouldQueueAfterCommit
{
    public function __construct(
        public readonly SalesOrderAdjustment $adjustment,
    ) {}

    public function handle(): void
    {
        ReconcileImplementedSalesOrderAdjustmentAction::run($this->adjustment);
    }
}
```

If the job and action share the same operation name, keep the class names aligned in different namespaces and alias the action import in the job file:

```php
use App\Actions\SalesOrderAdjustment\ReconcileImplementedSalesOrderAdjustment as ReconcileImplementedSalesOrderAdjustmentAction;
```

## Actions

Use actions for reusable, synchronous application behaviour and domain workflow steps.

Call actions directly via `::run(...)`, or from controllers, services, commands, jobs, and tests when the work should happen in the current process.

If a job needs shared application logic, the job should call an action rather than absorb that logic itself.

Do not add queue contracts, job middleware, retry/backoff configuration, Horizon tags, or queue-specific behaviour to action classes in this codebase. Put that behaviour on the native job that calls the action.

Jobs may resolve managers or factories inside `handle(...)` when the job itself owns the asynchronous integration work. Do not resolve managers, factories, services, or clients in the job constructor.

Jobs may call specifications inside `handle(...)` when the queued work must re-check eligibility before doing asynchronous work. Specifications must remain side-effect free; the job or action owns the side effect.

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
