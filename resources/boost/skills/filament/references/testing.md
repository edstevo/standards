# General Filament Testing

This reference defines the shared structure and defaults for Filament page tests.

Load this first when a task adds, changes, or refactors tests for Filament resources, pages, widgets, relation managers, forms, tables, actions, authorization, panel routing, tenancy, or Livewire interactions.

After loading this file, also load the focused reference that matches the page or behaviour:
- `testing-list-pages.md` for list pages, tables, search, filters, columns, and table/header/bulk actions
- `testing-view-pages.md` for view pages, infolists, relation managers, and record/page actions
- `testing-edit-pages.md` for edit pages, forms, save/update workflows, validation, and header actions
- `testing-create-pages.md` for create pages, forms, record creation workflows, validation, and header actions
- `testing-relation-managers.md` for relation managers, owner-record scoping, relation tables, relation actions, and relation manager validation
- `testing-authorization-panel-access.md` for auth, guests, roles, policies, tenants, guards, panel access, redirects, and `canAccessPanel()` behaviour

## Non-negotiable Test File Structure

Every Filament page and relation manager must have its own Pest test file. The test path must mirror the application path under `tests/Filament`.

Do not combine page or relation manager coverage into a generic resource test file. Do not move Filament tests into another tree because it looks neater. Pest and IDE test-location guessing depend on the mirrored structure.

Example application files:

```text
app/Filament/App/Resources/Customers/Pages/ListCustomers.php
app/Filament/App/Resources/Customers/Pages/ViewCustomer.php
app/Filament/App/Resources/Customers/RelationManagers/CustomerOrdersRelationManager.php
app/Filament/App/Resources/Customers/Schemas/CustomerInfolist.php
app/Filament/App/Resources/Customers/Tables/CustomersTable.php
app/Filament/App/Resources/Customers/CustomerResource.php
```

Required test files:

```text
tests/Filament/App/Resources/Customers/Pages/ListCustomersTest.php
tests/Filament/App/Resources/Customers/Pages/ViewCustomerTest.php
tests/Filament/App/Resources/Customers/RelationManagers/CustomerOrdersRelationManagerTest.php
```

Namespace Pest files to match the test path:

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;
```

Use this mapping for every resource page:

```text
app/Filament/{Panel}/Resources/{ResourcePlural}/Pages/{Page}.php
tests/Filament/{Panel}/Resources/{ResourcePlural}/Pages/{Page}Test.php
```

Use this mapping for standalone panel pages:

```text
app/Filament/{Panel}/Pages/{Page}.php
tests/Filament/{Panel}/Pages/{Page}Test.php
```

Use this mapping for relation managers:

```text
app/Filament/{Panel}/Resources/{ResourcePlural}/RelationManagers/{RelationManager}.php
tests/Filament/{Panel}/Resources/{ResourcePlural}/RelationManagers/{RelationManager}Test.php
```

## Default Pest Shape

Use file-level `beforeEach()` for authentication and setup that every group in the file needs. Use nested `beforeEach()` inside `describe()` blocks for page-specific records, scenarios, actions, authorization states, or relation-manager owner records when the file is a relation manager test.

Split different testing focuses into their own `describe()` blocks. Do this across all Filament tests, including pages and relation managers. Use clear group names such as:
- `Authorization`
- `Rendering`
- `Table Interaction`
- `Form Submission`
- `Validation`
- `Header Actions`
- `Record Actions`

Do not leave a mixed file as one broad page-level `describe()` block when it covers authorization, table behaviour, form behaviour, validation, and actions. Grouping by focus keeps failures readable and makes each file easier to scan.

Relation manager behaviour is never covered in a page test file. Each relation manager class has its own mirrored test file under `tests/Filament/.../RelationManagers`, and table interaction, search, filters, actions, validation, owner scoping, and page-context coverage all belong there.

Prefer Livewire tests against the Filament page class:

```php
Livewire::test(ListCustomers::class)
    ->assertSuccessful();
```

For record pages, pass the record route key explicitly:

```php
Livewire::test(ViewCustomer::class, ['record' => $customer->getRouteKey()])
    ->assertSuccessful();
```

Use HTTP tests against `Resource::getUrl(...)` for route-level authorization and panel redirects:

```php
$this->get(CustomerResource::getUrl('index'))->assertOk();
```

## Test Data And Setup

- Prefer factories and scenario builders over manual model graph assembly.
- Use `createQuietly()` when model events are irrelevant to the page behaviour being tested.
- Use normal `create()` when the test intentionally depends on model events, observers, or workflow side effects.
- Authenticate in file-level `beforeEach()` only when every group needs a signed-in user.
- Use role-specific helpers like `actAsAdminUser()` or `actAsCustomerUser()` when the project provides them.
- Keep tenant, panel, and permission setup visible enough that the access rules can be understood from the test.

## Actions

If a Filament page has actions, test them through the Livewire/Filament surface.

Action tests should assert the UI affordance and the outcome:
- action is visible or hidden for the current user/state
- validation passes or fails for important fields
- model state changes are persisted
- expected events, jobs, notifications, redirects, or integration fakes are triggered
- unauthorized users cannot call the action

For form-backed actions:

```php
it('can update tracking details', function () {
    $trackingNumber = 'APP-TRACK-'.Str::upper(Str::random(8));

    Livewire::test(ViewFulfillment::class, ['record' => $this->fulfillment->getRouteKey()])
        ->assertActionVisible('updateTracking')
        ->callAction('updateTracking', [
            'tracking_company' => SupportedCarrier::Evri->value,
            'tracking_number' => $trackingNumber,
        ]);

    expect($this->fulfillment->fresh()->tracking_company)->toBe(SupportedCarrier::Evri)
        ->and($this->fulfillment->fresh()->tracking_number)->toBe($trackingNumber);
});
```

For workflow/state actions:

```php
it('can mark fulfillment as delivered', function () {
    Event::fake([
        'eloquent.delivered: '.Fulfillment::class,
    ]);

    $fulfillment = Fulfillment::query()->sole()->refresh();

    Livewire::test(ViewFulfillment::class, ['record' => $fulfillment->getRouteKey()])
        ->assertActionVisible('markAsDelivered')
        ->callAction('markAsDelivered');

    Event::assertDispatched('eloquent.delivered: '.Fulfillment::class);
});
```

## When To Add Lower-Level Tests

Filament page tests prove the user-facing surface. Add lower-level model, policy, action, service, or workflow tests when:
- the action delegates non-trivial business logic
- authorization has complex policy conditions
- the page triggers model events, jobs, integrations, or state machines
- the same domain operation can be reached outside Filament
- setup in the Filament test is becoming too large because the domain behaviour needs its own focused tests
