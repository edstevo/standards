# Testing Filament Authorization And Panel Access

Load this reference after `testing.md` whenever a task involves Filament auth, guests, roles, policies, tenants, guards, panel access, cross-panel redirects, navigation visibility, action visibility, or `User::canAccessPanel()`.

Authorization deserves explicit tests. Do not assume that navigation visibility proves route, page, policy, action, tenant, or panel access.

## Checklist

For each protected Filament page, test the applicable behaviours:

- [ ] The allowed user can access the resource URL.
- [ ] A guest gets the expected unauthenticated response.
- [ ] A user with the wrong role cannot access the page.
- [ ] A user with the wrong guard cannot access the page when multiple guards apply.
- [ ] A user from the wrong tenant cannot access tenant-scoped pages or records.
- [ ] A user from the wrong panel is blocked or redirected correctly.
- [ ] Panel-specific redirects are correct for separate admin, app, supplier, or customer panels.
- [ ] `User::canAccessPanel()` allows and denies the expected users when panel access is central to the app.
- [ ] Navigation visibility agrees with direct route access.
- [ ] Page, table, row, bulk, relation manager, and record actions are visible/hidden according to authorization.
- [ ] Unauthorized users cannot call protected actions directly through Livewire.

Load `supplier-portals.md` as well when testing shared-login multi-panel supplier/customer apps.

## Route-Level Authorization

Use HTTP tests against resource URLs for page access, guest redirects, and panel redirects.

Example:

```php
<?php

namespace Tests\Filament\Admin\Resources\Bills\Pages;

use App\Filament\Admin\Resources\Bills\BillResource;

describe('Authorization', function () {
    test('can view page when an admin', function () {
        $this->actAsAdminUser();

        $this->get(BillResource::getUrl('index'))->assertOk();
    });

    test('cannot view page when not an admin', function () {
        $this->actAsCustomerUser();

        $this->get(BillResource::getUrl('index'))->assertRedirectContains('account');
    });

    test('redirects to login for guest', function () {
        $this->get(BillResource::getUrl('index'))->assertRedirectContains('login');
    });
});
```

When a project expects an HTTP 401 instead of a login redirect for unauthenticated access, assert that explicitly with `assertUnauthorized()`.

## Panel Access

When users belong to different panels, test both the correct panel and the wrong panel.

Examples:
- admin user can access admin resources
- admin user is redirected away from customer/app/supplier resources when that is the intended behaviour
- customer, app, or supplier user can access their own panel resources
- customer, app, or supplier user is redirected away from admin resources
- guest is redirected to login

Keep redirect assertions concrete enough to prove the intended panel:

```php
$this->actAsCustomerUser();

$this->get(BillResource::getUrl('index'))
    ->assertRedirectContains('account');
```

## Livewire Authorization

Use Livewire tests for component-level action visibility and direct action calls:

```php
Livewire::test(ViewFulfillment::class, ['record' => $fulfillment->getRouteKey()])
    ->assertActionHidden('markAsDelivered');
```

When the user should be allowed:

```php
Livewire::test(ViewFulfillment::class, ['record' => $fulfillment->getRouteKey()])
    ->assertActionVisible('markAsDelivered');
```

If a protected action can be called directly, include a negative test for unauthorized users using the project's expected failure mode.

## Navigation Is Not Enough

Navigation tests are useful, but they do not replace route and Livewire authorization tests.

When navigation visibility matters, pair it with direct access coverage:
- assert the navigation item is hidden for a disallowed user
- assert the route is also blocked for the same user
- assert the page/action is available for an allowed user

## Tenancy

For tenant-aware panels:
- create records for both the current tenant and another tenant
- bind or select the current tenant the same way the app does
- assert the current tenant can see its records
- assert records from other tenants are hidden or blocked
- assert direct access to another tenant's record fails or redirects as expected

Keep tenant setup visible unless the project has a clear shared helper for this exact concern.
