## EdStevo Packages Code Guidelines

This package provides coding standards, conventions, and best practices for EdStevo Laravel projects.

### Project Structure Conventions

**Directory Organization:**

- Controllers should be organized by domain/feature in subdirectories under `app/Http/Controllers/`
- Services and actions should live in `app/Services/` and `app/Actions/` respectively
- Data Transfer Objects (DTOs) should be in `app/Dtos/`
- Enums should be in `app/Enums/`
- Value Objects should be in `app/ValueObjects/`
- Custom exceptions should be in `app/Exceptions/`

**Naming Conventions:**

- Use singular names for models (e.g., `User`, `Post`, not `Users`, `Posts`)
- Controllers should be named `{Resource}Controller` (e.g., `UserController`, `PostController`)
- Actions should be named as verbs describing what they do (e.g., `CreateUser`, `SendWelcomeEmail`)
- Services should be named `{Domain}Service` (e.g., `UserService`, `PaymentService`)
- Traits should be prefixed with the domain or suffixed with `Trait` (e.g., `HasUuid`, `Loggable`)

@verbatim
<code-snippet name="Example Domain Organization" lang="plaintext">
app/
├── Actions/
│   └── User/
│       ├── CreateUser.php
│       └── UpdateUserProfile.php
├── Definitions/
│   └── UserGroup.php (enum)
│   └── FulfillmentStatus.php (enum)
├── Integrations/
│   └── Quickbooks
│   └── Shopify
│       └── Commands
│       └── Contracts
│       └── Definitions
│       └── Dtos
│       └── Jobs
│       └── Shopify.php (Facade)
│       └── ShopifyRepository.php (EntryPoint)
│       └── ShopifyServiceProvider.php (Integration Service Provider)
└── Models/
    └── Concerns/
        └── BelongsToManyUsers.php
    └── User.php
</code-snippet>
@endverbatim

### Code Style & Best Practices

**Single Responsibility:**

Each class should have a single, well-defined responsibility. Prefer small, focused classes over large, monolithic ones.

**Model Relationships:**

I prefer to define relationships using reusable traits wherever possible rather than on the model themselves.

@verbatim
<code-snippet name="DTO Example" lang="php">
<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $user_id
 * @property User|null $user
 */
trait BelongsToUser
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
</code-snippet>
@endverbatim

**Data Transfer Objects:**

I prefer to use DTOs to pass data between layers of my applications. DTOs should be immutable and validate data at construction. I use spatie/laravel-data to support this.

@verbatim
<code-snippet name="DTO Example" lang="php">
<?php

namespace App\Dtos;

class ShopifyCustomerDto extends Data
{
    public string $firstName;
    public string $lastName;
    public ?string $email;
}
</code-snippet>
@endverbatim

**Enums:**

Use native PHP enums for fixed sets of values. Prefer backed enums for database storage.

@verbatim
<code-snippet name="Enum Example" lang="php">
<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }
}
</code-snippet>
@endverbatim

**Controller Best Practices:**

- Keep controllers thin - delegate business logic to actions and services
- Use dependency injection for services and actions
- Return appropriate HTTP responses (200, 201, 204, etc.)
- Use resource controllers when following REST conventions

@verbatim
<code-snippet name="Thin Controller Example" lang="php">
<?php

namespace App\Http\Controllers\User;

use App\Dtos\User\CreateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $data = CreateUserData::fromRequest($request->validated());

        $user = $this->userService->register($data);

        return response()->json($user, 201);
    }
}
</code-snippet>
@endverbatim

**Query Optimization:**

- Always eager load relationships to avoid N+1 queries
- Use database indexes for frequently queried columns
- Prefer query builders for complex queries over raw SQL
- Use `select()` to only retrieve needed columns

**Jobs and Queues:**

- I prefer to use jobs as much as possible to isolate business logic from the request lifecycle
- Jobs should be short and simple - avoid complex logic in jobs. If there is more complex logic to do then it should trigger another job.
- Use queues for long-running tasks that may take a long time to complete

**Security Best Practices:**

- Never trust user input - always validate using Form Requests
- Use Laravel's built-in CSRF protection
- Sanitize output to prevent XSS attacks
- Use mass assignment protection on models
- Store sensitive data encrypted in the database

### Contracts

- All external integrations must expose a Contract (interface)
- Services depending on integrations must depend on Contracts, not implementations
- Bindings must be registered in a dedicated Service Provider

### Configuration

- No hardcoded values
- Use config files for domain settings
- Use environment variables only for infrastructure concerns
- Never call env() outside config files

### Testing Standards

**Test Organization:**

- Unit tests go in `tests/Unit/`
- Feature tests test e2e and large scale application logic go in `tests/Feature/`
- Otherwise organise tests to mirror the application structure
- As per the directory structure above, Shopify Tests should go in `tests/Integrations/Shopify/`
- Use Pest PHP for testing with clear, descriptive test names
- expect() assertions should be used for assertions and should be chained. E.g:

@verbatim
<code-snippet name="Expect Assertion Chaining" lang="php">
<?php

expect($lineItem->totalGross)->toBe($grossTotal)
    ->and($lineItem->totalTax)->toBeGreaterThan(0)
</code-snippet>
@endverbatim

**Test Coverage:**

- Write unit tests for actions, services, and complex business logic
- Write feature tests for API endpoints and user workflows
- Aim for high coverage of critical business logic
- Test both happy paths and edge cases

@verbatim
<code-snippet name="Feature Test Example" lang="php">
<?php

use App\Models\User;

it('can register a new user via API', function () {
    $response = $this->postJson('/api/users', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'created_at',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
    ]);
});

it('validates required fields when registering', function () {
    $response = $this->postJson('/api/users', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});
</code-snippet>
@endverbatim

**Testing Best Practices:**

- Use factories for creating test data
- Use descriptive test names that explain what is being tested
- Follow the AAA pattern: Arrange, Act, Assert
- Use dataset testing for testing multiple scenarios
- Mock external services and APIs in tests
- Reset database state between tests using `RefreshDatabase` trait

**Database Testing:**

- Use in-memory SQLite for faster test execution
- Seed only the data needed for specific tests
- Use database transactions to roll back changes after each test
