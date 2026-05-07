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

Filament tests must live in the mirrored application path under `tests/Filament`, but coverage should be split into small scenario-focused files instead of one large page or resource suite.

Do not combine page or relation manager coverage into a generic resource test file. Do not move Filament tests into another tree because it looks neater. Pest and IDE test-location guessing depend on the mirrored structure.

Use a narrow page or relation-manager smoke file only when it proves the page/component itself renders or mounts. Put meaningful user behaviours, actions, authorization scenarios, table interactions, validation paths, and workflow outcomes in descriptive scenario files beside the page or relation manager.

Example application files:

```text
app/Filament/App/Resources/Customers/Pages/ListCustomers.php
app/Filament/App/Resources/Customers/Pages/ViewCustomer.php
app/Filament/App/Resources/Customers/RelationManagers/CustomerOrdersRelationManager.php
app/Filament/App/Resources/Customers/Schemas/CustomerInfolist.php
app/Filament/App/Resources/Customers/Tables/CustomersTable.php
app/Filament/App/Resources/Customers/CustomerResource.php
```

Example scenario-focused test files:

```text
tests/Filament/App/Resources/Customers/Pages/ListCustomersPageRendersTest.php
tests/Filament/App/Resources/Customers/Pages/CustomerListCanBeSearchedByEmailTest.php
tests/Filament/App/Resources/Customers/Pages/AdminCanViewCustomerDetailsTest.php
tests/Filament/App/Resources/Customers/RelationManagers/CustomerOrdersRelationManagerListsOnlyCustomerOrdersTest.php
```

Namespace Pest files to match the test path:

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;
```

Use this mapping for resource page scenarios:

```text
app/Filament/{Panel}/Resources/{ResourcePlural}/Pages/{Page}.php
tests/Filament/{Panel}/Resources/{ResourcePlural}/Pages/{Scenario}Test.php
```

Use this mapping for standalone panel page scenarios:

```text
app/Filament/{Panel}/Pages/{Page}.php
tests/Filament/{Panel}/Pages/{Scenario}Test.php
```

Use this mapping for relation manager scenarios:

```text
app/Filament/{Panel}/Resources/{ResourcePlural}/RelationManagers/{RelationManager}.php
tests/Filament/{Panel}/Resources/{ResourcePlural}/RelationManagers/{Scenario}Test.php
```

## Default Pest Shape

Use file-level `beforeEach()` for authentication and setup that every assertion in the scenario file needs. Use nested `beforeEach()` inside `describe()` blocks for tightly related variants of the same scenario.

Split independent testing focuses into their own files. Use clear scenario names such as:
- `AdminCanAccessCustomerListTest.php`
- `GuestIsRedirectedFromCustomerListTest.php`
- `CustomerListCanBeSearchedByEmailTest.php`
- `CustomerCanBeCreatedFromFilamentTest.php`
- `CustomerEmailValidationIsEnforcedTest.php`

Inside one scenario file, `describe()` blocks may organize tightly related concerns such as:
- `Authorization`
- `Rendering`
- `Table Interaction`
- `Form Submission`
- `Validation`
- `Header Actions`
- `Record Actions`

Do not leave a mixed file as one broad page-level `describe()` block when it covers authorization, table behaviour, form behaviour, validation, and actions. The page/resource folder is the grouping; the file should identify the scenario.

Relation manager behaviour is never covered in a page test file. Relation manager scenario files live under the mirrored `tests/Filament/.../RelationManagers` path, and table interaction, search, filters, actions, validation, owner scoping, and page-context coverage all belong there.

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
- Do not add top-level Pest helper functions, private helper methods, or file-local fixture functions to hide setup.
- If setup is short and only used once, keep it inline in the test or the nearest useful `beforeEach()`.
- If setup repeats because it represents a data shape, prefer a named factory state, factory `configure()` hook, or explicit scenario builder.
- If reusable setup or assertions are genuinely needed, put broad helpers in `tests/TestCase.php` and domain-specific support in `tests/Support`.
- If tempted to extract a helper inside the test file, load `laravel-tdd/references/test-support.md` first and choose the proper support layer.
- Use `createQuietly()` when model events are irrelevant to the page behaviour being tested.
- Use normal `create()` when the test intentionally depends on model events, observers, or workflow side effects.
- Authenticate in file-level `beforeEach()` only when every group needs a signed-in user.
- Use role-specific helpers like `actAsAdminUser()` or `actAsCustomerUser()` when the project provides them.
- Keep tenant, panel, and permission setup visible enough that the access rules can be understood from the test.

Avoid this pattern in Filament tests:

```php
function editPageAccreditationWithUpload(array $attributes = []): Accreditation
{
    // Hidden fixture setup that belongs inline, in a factory state, or in tests/Support.
}
```

If the setup is part of the story the test is proving, keep the important records and state changes visible in the test or scoped `beforeEach()`. If the setup is a reusable domain fixture, promote it to a named factory state or support class instead of creating a local function.

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
