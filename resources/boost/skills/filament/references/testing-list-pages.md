# Testing Filament List Pages

Load this reference after `testing.md` when a task involves a Filament `List*` page, table, searchable columns, filters, visible columns, table actions, bulk actions, or header actions.

List page tests must live beside the page's mirrored test path. For example:

```text
app/Filament/App/Resources/Customers/Pages/ListCustomers.php
tests/Filament/App/Resources/Customers/Pages/ListCustomersTest.php
```

## Checklist

Every list page test file should cover the applicable items below:

- [ ] The page renders successfully with `Livewire::test(PageClass::class)->assertSuccessful()`.
- [ ] Authenticated users who should access the page can reach the resource URL.
- [ ] Guests receive the expected unauthenticated response: usually redirect to login for Filament browser panels, or 401 if the project is configured that way.
- [ ] Users from the wrong role, guard, tenant, or panel are blocked or redirected correctly.
- [ ] The table can show expected records with `assertCanSeeTableRecords(...)`.
- [ ] Searchable columns can be searched and exclude non-matching records. Test each important searchable field separately.
- [ ] Filters can be applied and exclude non-matching records. Test each meaningful filter state separately.
- [ ] The expected columns render with `assertCanRenderTableColumn(...)`.
- [ ] Header, table, row, and bulk actions are visible/hidden as expected.
- [ ] Actions can be executed and their domain effects are asserted.

Load `testing-authorization-panel-access.md` when implementing the authorization bullets.

## Default Structure

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
});

describe('CustomerResource Index Page', function () {
    it('can render the index page', function () {
        Livewire::test(ListCustomers::class)
            ->assertSuccessful();
    });

    it('can list customers', function () {
        $customers = Customer::factory()->count(3)->createQuietly();

        Livewire::test(ListCustomers::class)
            ->assertCanSeeTableRecords($customers);
    });

    it('can search customers by name', function () {
        $customers = Customer::factory()->count(3)->createQuietly();

        Livewire::test(ListCustomers::class)
            ->searchTable($customers->first()->first_name)
            ->assertCanSeeTableRecords([$customers->first()])
            ->assertCanNotSeeTableRecords($customers->skip(1));
    });

    it('can search customers by email', function () {
        $customers = Customer::factory()->count(3)->createQuietly();

        Livewire::test(ListCustomers::class)
            ->searchTable($customers->first()->email)
            ->assertCanSeeTableRecords([$customers->first()])
            ->assertCanNotSeeTableRecords($customers->skip(1));
    });

    it('displays correct columns', function () {
        Customer::factory()->createQuietly();

        Livewire::test(ListCustomers::class)
            ->assertCanRenderTableColumn('fullName')
            ->assertCanRenderTableColumn('email')
            ->assertCanRenderTableColumn('phone');
    });
});
```

## Table Records

Use `assertCanSeeTableRecords(...)` and `assertCanNotSeeTableRecords(...)` to prove the Filament table surface, not only the database state.

Create enough records to prove inclusion and exclusion:

```php
$matching = Customer::factory()->createQuietly(['first_name' => 'Ada']);
$other = Customer::factory()->createQuietly(['first_name' => 'Grace']);

Livewire::test(ListCustomers::class)
    ->searchTable('Ada')
    ->assertCanSeeTableRecords([$matching])
    ->assertCanNotSeeTableRecords([$other]);
```

## Search And Filters

Test each important searchable field separately. A single broad search test is not enough when the table exposes several searchable fields that matter to users.

For filters, assert both the included and excluded records for each meaningful filter state.

## Columns

Assert the columns that define the page contract with `assertCanRenderTableColumn(...)`.

Do not only assert visible text for table coverage when a Filament table assertion exists.

## List Page Actions

When list pages expose header, row, table, or bulk actions:
- assert the action is visible or hidden for the relevant user and record state
- execute the action through the Filament/Livewire testing API
- assert the persisted domain outcome, dispatched event/job, notification, redirect, or integration fake result
- test unauthorized or invalid action calls when the state or permission boundary matters
