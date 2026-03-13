# Time Travel and Clock Control

This reference is for tests that manipulate application time with `now()`, `$this->travel()`, `$this->travelTo()`, `freezeTime()`, or related Laravel helpers.

Load it when:
- a test is time-sensitive
- a workflow has several dated events
- you need to choose between derived timestamps and a frozen application clock
- a test is stacking several `travelTo()` calls and becoming hard to reason about

## Core rule

Use time travel to control the moment of the act.

Do not use repeated `$this->travelTo(...)` calls as a timestamp-building step during arrange.

Why:
- each `travelTo()` call replaces the current frozen test clock
- if no behaviour runs between those calls, only the latest frozen time affects the system under test
- the test becomes harder to read because the clock history is implicit instead of tied to each act

## Bad pattern

This is misleading because the clock keeps being replaced before the system does anything:

```php
$infoReceivedAt = now()->startOfMinute()->subHours(4);
$this->travelTo($infoReceivedAt);
$infoReceivedAt = now()->copy();

$inTransitAt = $infoReceivedAt->copy()->addHour();
$this->travelTo($inTransitAt);
$inTransitAt = now()->copy();

$deliveredAt = $inTransitAt->copy()->addHour();
$this->travelTo($deliveredAt);
$deliveredAt = now()->copy();

// No behaviour has happened yet.
```

If nothing ran between those calls, the test did not really exercise three moments in time. It only ended up frozen at the last one.

## Better pattern

If you only need timestamp values, derive them from one base time without travelling:

```php
$infoReceivedAt = now()->startOfMinute()->subHours(4);
$inTransitAt = $infoReceivedAt->copy()->addHour();
$failedAttemptAt = $inTransitAt->copy()->addMinutes(30);
$deliveredAt = $failedAttemptAt->copy()->addHour();
```

Then move the clock immediately before the relevant act:

```php
$this->travelTo($deliveredAt);

$this->postJson('/webhook', [
    'status' => 'delivered',
    'checkpoint_date' => $deliveredAt->toAtomString(),
])->assertSuccessful();

$this->travelTo($failedAttemptAt);

$this->postJson('/webhook', [
    'status' => 'failed_attempt',
    'checkpoint_date' => $failedAttemptAt->toAtomString(),
])->assertSuccessful();
```

This makes each act read as:
- set the clock
- perform the behaviour
- assert the outcome

## Prefer scoped helpers when possible

Laravel's closure-based helpers are often the clearest option for short phases:

```php
$this->travelTo($failedAttemptAt, function (): void {
    $this->postJson('/webhook', [
        'status' => 'failed_attempt',
        'checkpoint_date' => $failedAttemptAt->toAtomString(),
    ])->assertSuccessful();
});
```

Also consider:
- `$this->freezeTime(...)` when the exact current moment matters more than an offset
- `$this->travelBack()` after manual travel when later test phases should return to real time

## Practical rule of thumb

Choose one of these patterns:

- derive several timestamps from a base Carbon value and do not travel at all
- travel once immediately before one act
- travel, act, then travel again for the next act
- use closure-based travel helpers so the frozen scope is explicit

Avoid:
- back-to-back `travelTo()` calls with no behaviour between them
- using `now()` after every `travelTo()` just to "lock in" another timestamp
- letting frozen time leak into unrelated later assertions or acts

The goal is a test where each significant moment in time is visible at the same point as the behaviour it affects.
