# Testing State Pattern Workflows

Load this reference when testing valid transitions, invalid transitions, context application, model events, Spatie `StateChanged`, or multi-state models.

State Pattern tests should stay focused. Avoid one broad test that proves a full workflow, observer, job, integration call, timeline entry, and final domain state together.

## Test File Shape

Prefer scenario-focused test files:
- `tests/Models/SalesOrderCanBeApprovedTest.php`
- `tests/Models/SalesOrderCannotBeApprovedAfterRejectionTest.php`
- `tests/Models/InvoiceCanBeMarkedPaidTest.php`
- `tests/Models/FulfillmentOrderCanBeSubmittedToSupplierTest.php`

Use one transition or one meaningful invalid transition scenario per test file as much as reasonably feasible.

## Valid Transitions

Assert the expressive model method changes the Spatie state field and persists the result:

```php
it('marks an issued invoice as paid', function () {
    $invoice = Invoice::factory()
        ->issued()
        ->create();

    $invoice->markAsPaid(
        paidAt: now(),
    );

    expect($invoice->refresh()->state)
        ->toBeInstanceOf(Paid::class);
});
```

## Invalid Transitions

Assert invalid actions fail at the package transition boundary:

```php
use Spatie\ModelStates\Exceptions\TransitionNotFound;

it('does not mark a draft invoice as paid', function () {
    $invoice = Invoice::factory()
        ->draft()
        ->create();

    expect(fn () => $invoice->markAsPaid(paidAt: now()))
        ->toThrow(TransitionNotFound::class);

    expect($invoice->refresh()->state)
        ->toBeInstanceOf(Draft::class);
});
```

## Transition Context

Assert contextual data is applied by the custom Spatie transition class:

```php
it('records the payment timestamp when an invoice is paid', function () {
    $paidAt = now()->subHour();

    $invoice = Invoice::factory()
        ->issued()
        ->create();

    $invoice->markAsPaid(
        paidAt: $paidAt,
    );

    $invoice->refresh();

    expect($invoice->state)
        ->toBeInstanceOf(Paid::class);

    expect($invoice->paid_at)
        ->toEqual($paidAt);
});
```

## Before And After Model Events

Fake and assert the custom model events fired by the expressive model method:

```php
use Illuminate\Support\Facades\Event;

it('fires invoice paying and paid model events', function () {
    Event::fake([
        'eloquent.paying: '.Invoice::class,
        'eloquent.paid: '.Invoice::class,
    ]);

    $invoice = Invoice::factory()
        ->issued()
        ->create();

    $invoice->markAsPaid(
        paidAt: now(),
    );

    Event::assertDispatched('eloquent.paying: '.Invoice::class);
    Event::assertDispatched('eloquent.paid: '.Invoice::class);
});
```

Observer tests should prove delegation, not the entire downstream workflow:

```php
use Illuminate\Support\Facades\Bus;

it('dispatches the notification job when an invoice is paid', function () {
    Bus::fake([
        SendInvoicePaidNotification::class,
    ]);

    $invoice = Invoice::factory()
        ->paid()
        ->create();

    app(InvoiceObserver::class)->paid($invoice);

    Bus::assertDispatched(SendInvoicePaidNotification::class);
});
```

## Spatie StateChanged

Use Spatie's `StateChanged` event when testing generic state-change infrastructure:

```php
use Spatie\ModelStates\Events\StateChanged;

it('dispatches the package state changed event', function () {
    Event::fake([
        StateChanged::class,
    ]);

    $invoice = Invoice::factory()
        ->issued()
        ->create();

    $invoice->markAsPaid(
        paidAt: now(),
    );

    Event::assertDispatched(StateChanged::class);
});
```

## Coverage Checklist

- [ ] One positive test per meaningful allowed transition.
- [ ] One negative test per meaningful invalid action.
- [ ] Context tests for transitions that apply timestamps, reasons, users, or external references.
- [ ] Before model event tests when a transition can be halted or validated by observers.
- [ ] After model event tests for meaningful lifecycle reactions.
- [ ] Spatie `StateChanged` tests only for generic state-change infrastructure.
- [ ] Observer, job, and listener outcomes tested in their own files.
