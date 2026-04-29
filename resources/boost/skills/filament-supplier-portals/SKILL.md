---
name: filament-supplier-portals
description: Build Filament apps with two distinct user portals (for example admin and supplier/customer) using separate panels, a single login entrypoint, role-aware login/logout redirects, panel-protection middleware, and User::canAccessPanel authorization rules.
---

# Filament Supplier Portals

Implement multi-portal Filament apps with one primary panel (admin) and one secondary panel (supplier/customer), while keeping authentication and authorization predictable.

## Follow this architecture

- Define exactly one panel with `->login()` (usually `admin`).
- Keep the secondary panel on a path (for example `->path('supplier')`) without `->login()`.
- Bind custom Filament auth responses for login/logout routing.
- Add one auth middleware that redirects authenticated users to the panel matching their role.
- Enforce panel access in `User::canAccessPanel()` as a final gate.

## Required files

- `app/Http/Responses/LoginResponse.php`
- `app/Http/Responses/LogoutResponse.php`
- `app/Http/Middleware/RedirectToProperPanelMiddleware.php`
- `app/Providers/AppServiceProvider.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Providers/Filament/SupplierPanelProvider.php`
- `app/Models/User.php`

## Implementation

### 1) Create a role-aware login response

Use a custom login response to route supplier/customer users to the supplier panel after authentication.

```php
<?php

namespace App\Http\Responses;

use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends \Filament\Auth\Http\Responses\LoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = auth()->user();

        if ($user?->isSupplier()) {
            $url = Redirect::getIntendedUrl() ?? '';

            if (Str::doesntContain($url, '/supplier')) {
                return redirect()->to(Dashboard::getUrl(panel: 'supplier'));
            }

            return redirect()->intended(Dashboard::getUrl(panel: 'supplier'));
        }
        
        if ($user?->isAdmin()) {
            $url = Redirect::getIntendedUrl() ?? '';

            if (Str::contains($url, '/supplier')) {
                return redirect()->to(Dashboard::getUrl(panel: 'admin'));
            }

            return redirect()->intended(Dashboard::getUrl(panel: 'admin'));
        }        

        return parent::toResponse($request);
    }
}
```

### 2) Create a logout response that returns secondary-panel users to primary login

Use a custom logout response so logging out from supplier/customer sends users to the admin login page.

```php
<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class LogoutResponse extends \Filament\Auth\Http\Responses\LogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        if (Filament::getCurrentPanel()->getId() === 'supplier') {
            return redirect()->to(Filament::getPanel('admin')->getLoginUrl());
        }

        return parent::toResponse($request);
    }
}
```

### 3) Add middleware to redirect authenticated users to their correct panel

Apply this middleware in each panel provider's `authMiddleware()` stack.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Http\Request;

class RedirectToProperPanelMiddleware
{
    /**
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($user = auth()->user()) {
            $panelId = Filament::getCurrentPanel()?->getId();

            if ($panelId === 'admin' && $user->isSupplier()) {
                return redirect()->to(Dashboard::getUrl(panel: 'supplier'));
            }

            if ($panelId === 'supplier' && $user->isAdmin()) {
                return redirect()->to(Dashboard::getUrl(panel: 'admin'));
            }
        }

        return $next($request);
    }
}
```

If the supplier panel is tenant-aware, pass tenant context in the redirect URL:

```php
$team = $user->latestTeam ?? $user->teams->first();
return redirect()->to(Dashboard::getUrl(['tenant' => $team->getKey()], panel: 'supplier'));
```

### 4) Register custom responses in `AppServiceProvider`

Bind Filament response contracts to your custom classes.

```php
<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        LoginResponse::class => \App\Http\Responses\LoginResponse::class,
        LogoutResponse::class => \App\Http\Responses\LogoutResponse::class,
    ];
}
```

### 5) Configure panel providers correctly

Use one panel as the default login owner (`admin`) and keep supplier/customer panel auth-only.

Admin panel example:

```php
return $panel
    ->default()
    ->id('admin')
    ->login()
    ->profile()
    ->passwordReset()
    ->emailVerification()
    ->authMiddleware([
        \App\Http\Middleware\RedirectToProperPanelMiddleware::class,
        \Filament\Http\Middleware\Authenticate::class,
    ]);
```

Supplier panel example:

```php
return $panel
    ->id('supplier')
    ->path('supplier')
    ->profile()
    ->passwordReset()
    ->emailVerification()
    ->authMiddleware([
        \App\Http\Middleware\RedirectToProperPanelMiddleware::class,
        \Filament\Http\Middleware\Authenticate::class,
    ]);
```

Do not add `->login()` to the supplier panel when using shared login flow.

### 6) Enforce panel access in `User::canAccessPanel()`

Use explicit role helpers and panel checks.

```php
public function isAdmin(): bool
{
    return (bool) $this->is_admin;
}

public function isSupplier(): bool
{
    return ! $this->isAdmin();
}

public function canAccessPanel(\Filament\Panel $panel): bool
{
    if ($panel->getId() === 'admin' && $this->isAdmin()) {
        return true;
    }

    if ($panel->getId() === 'supplier' && $this->isSupplier()) {
        return true;
    }

    return false;
}
```

Keep role helper names consistent across all classes (`isSupplier` vs `isPartner`).

## Commands to scaffold quickly

```bash
php artisan make:class Http/Responses/LoginResponse
php artisan make:class Http/Responses/LogoutResponse
php artisan make:middleware RedirectToProperPanelMiddleware
php artisan make:filament-panel admin
php artisan make:filament-panel supplier
```

## Validation checklist

- Supplier user logging in at `/login` lands on supplier dashboard.
- Admin user logging in at `/login` lands on admin dashboard.
- Supplier visiting admin URLs is redirected to supplier panel.
- Admin visiting supplier URLs is redirected to admin panel.
- Logging out from supplier redirects to admin login.
- `canAccessPanel()` blocks wrong-role access even if route is reached.
- Panel IDs and paths are identical everywhere (`admin`, `supplier`).
