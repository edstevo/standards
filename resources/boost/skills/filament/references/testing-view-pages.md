# Testing Filament View Pages

Load this reference after `testing.md` when a task involves a Filament `View*` page, infolist, record details, relation managers, page actions, record actions, or detail-page workflows.

View page tests must live beside the page's mirrored test path. For example:

```text
app/Filament/App/Resources/Customers/Pages/ViewCustomer.php
tests/Filament/App/Resources/Customers/Pages/ViewCustomerTest.php
```

## Checklist

Every view page test file should cover the applicable items below:

- [ ] The page renders successfully with the record route key.
- [ ] Authenticated users who should access the page can reach the resource URL.
- [ ] Guests receive the expected unauthenticated response: usually redirect to login for Filament browser panels, or 401 if the project is configured that way.
- [ ] Users from the wrong role, guard, tenant, or panel are blocked or redirected correctly.
- [ ] Key record details from infolists, headings, badges, or sections are visible.
- [ ] Empty, missing, or optional record fields render intentionally when relevant.
- [ ] Relation managers are present or absent as expected.
- [ ] Relation managers are visible on the page when expected. Test relation manager tables, search, filters, actions, and validation in the mirrored relation manager test file.
- [ ] Page, header, infolist, relation manager, and record actions are visible/hidden as expected.
- [ ] Actions can be executed and their domain effects are asserted.

Load `testing-authorization-panel-access.md` when implementing the authorization bullets.

## Default Structure

```php
<?php

namespace Tests\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());
});

beforeEach(function () {
    $this->customer = Customer::factory()->createQuietly();
});

describe('Rendering', function () {
    it('can render the view page', function () {
        Livewire::test(ViewCustomer::class, ['record' => $this->customer->getRouteKey()])
            ->assertSuccessful();
    });
});

describe('Record Details', function () {
    it('displays customer details', function () {
        Livewire::test(ViewCustomer::class, ['record' => $this->customer->getRouteKey()])
            ->assertSee($this->customer->fullName)
            ->assertSee($this->customer->email);
    });
});

describe('Relation Managers', function () {
    it('has no relation managers', function () {
        $component = Livewire::test(ViewCustomer::class, ['record' => $this->customer->getRouteKey()]);

        expect($component->instance()->getRelationManagers())->toBeEmpty();
    });
});
```

## Record Details

Test the fields that make the view page useful to the user:
- names, references, statuses, totals, dates, and important badges
- values rendered by infolists, headings, sections, or custom components
- meaningful empty or optional states when the page has conditional display logic

Use direct `assertSee(...)` assertions for record detail text unless the project has a more specific Filament assertion for the rendered surface.

## Relation Managers

When a view page exposes relation managers, the view page test should only prove that the relation manager is present or absent on the page.

Full relation manager coverage belongs in the mirrored relation manager test file under `tests/Filament/.../RelationManagers`. Load `testing-relation-managers.md` for that structure.

Presence example:

```php
<?php

namespace Tests\Filament\App\Resources\PurchaseOrders\Pages;

use App\Filament\App\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Filament\App\Resources\PurchaseOrders\RelationManagers\PurchaseOrderLinesRelationManager;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->createQuietly());

    $result = $this->scenario()->dropship()->build();
    $this->purchaseOrder = $result->purchaseOrders->first();
});

describe('PurchaseOrderResource View Page', function () {
    it('can render the view page', function () {
        Livewire::test(ViewPurchaseOrder::class, ['record' => $this->purchaseOrder->getRouteKey()])
            ->assertSuccessful();
    });

    it('displays purchase order details', function () {
        Livewire::test(ViewPurchaseOrder::class, ['record' => $this->purchaseOrder->getRouteKey()])
            ->assertSee($this->purchaseOrder->name);
    });
});

describe('PurchaseOrderLinesRelationManager', function () {
    it('displays purchase order lines relation manager', function () {
        Livewire::test(ViewPurchaseOrder::class, ['record' => $this->purchaseOrder->getRouteKey()])
            ->assertSeeLivewire(PurchaseOrderLinesRelationManager::class);
    });
});
```

## View Page Actions

For page, header, infolist, relation manager, and record actions:
- assert the action is visible or hidden for the relevant user and record state
- execute the action through the Filament/Livewire testing API
- assert the persisted domain outcome, dispatched event/job, notification, redirect, or integration fake result
- test unauthorized or invalid action calls when the state or permission boundary matters

Use the action testing examples in `testing.md` for form-backed and workflow/state actions.
