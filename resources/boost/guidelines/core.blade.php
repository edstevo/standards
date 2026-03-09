## EdStevo Packages Code Guidelines

This package provides coding standards, conventions, and best practices for EdStevo Laravel projects.
Apply these rules when generating or modifying code.

### AI Execution Rules

- Treat these guidelines as defaults for new code and refactors.
- Follow existing project conventions first when they conflict with these defaults.
- Prefer explicit, deterministic implementations over clever or implicit behavior.
- When tradeoffs exist, prioritize correctness, readability, and maintainability in that order.

### Skills Activation

Laravel Boost may inject a `## Skills Activation` section with project-specific skills and trigger rules.

- Treat matching skills as required, not optional. Activate relevant skills as soon as the task enters that domain.
- Do not wait until blocked before applying a relevant skill.
- Skills are specialized guidance layered on top of these core defaults.
- If multiple skills apply, follow all relevant skills together.
- When workflow behavior changes, activate `process-documentation-with-flowcharts` and update both process docs and related class/method documentation in the same change.
- Resolve conflicts in this order: explicit user request, existing project conventions, relevant skill guidance, then these core defaults.
- If no relevant skill is listed for the task, follow these core guidelines as the baseline.

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

**Coding Style Defaults:**

- Prefer domain-first, intention-revealing names over abstract or clever names.
- Prefer explicit orchestration with small private methods so business flow is easy to audit.
- For non-trivial workflows, document local decision rules with short PHPDoc blocks and maintain process docs with flowcharts for system-level behavior.
- Individual classes should be documented with clear intent, especially jobs and action classes.
- Internal/private methods in non-trivial classes should include concise PHPDoc where needed and should align with the class-level intent documentation.
- Favor defensive code paths: fail fast on invalid configuration and protect multi-write operations with transactions.
- Prefer deterministic fallback behavior when inputs are ambiguous, and encode fallback reasons with enums where possible.
- Optimize for readability and correctness first; accept minor repetition when it keeps behavior obvious.
- Avoid premature micro-optimization in business workflows unless profiling shows a real bottleneck.

**Model Relationships:**

Define relationships using reusable traits wherever practical instead of duplicating relationship methods across models.

- Prefer one relationship per trait so traits stay composable and searchable.
- Use concrete Eloquent relation return types (`BelongsTo`, `HasMany`, `MorphOne`, `MorphMany`, etc.).
- Add relationship property docblocks on traits for IDE/static-analysis support.
- Keep relationship method names domain-natural (`shop()`, `fulfillmentLines()`) even when trait names are explicit.
- If a relationship implies an intentionally fillable FK, use a trait initializer with `mergeFillable([...])`.
- Prefer `app/Models/Relationships` for relationship traits unless the project already uses another established location.
- For full conventions and implementation templates, activate `eloquent-relationship-traits`.

@verbatim
<code-snippet name="Relationship Trait Example" lang="php">
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

Use DTOs to pass data between layers. DTOs should be immutable and validate data at construction. Use `spatie/laravel-data` where appropriate.

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

- Use jobs where practical to isolate business logic from the request lifecycle
- Jobs should be short and simple - avoid complex logic in jobs. If there is more complex logic to do then it should trigger another job.
- Use queues for long-running tasks that may take a long time to complete
- In observer-driven workflows, observers must never execute synchronous external follow-up actions inline. They should only dispatch events/jobs or call methods that trigger further explicit events (for example `closed` handler -> `markAsArchived()`), and side effects should run after commit.

**Model Construction and Persistence:**

- Use explicit model construction with `new Model`, explicit property assignment, and `save()` over large mass-assignment arrays for non-trivial domain flows.
- Prefer `associate()` for relationships (including polymorphic relations) rather than manually assigning foreign keys.
- Construct parent records and child records in clearly separated steps and methods.
- When multiple related writes must succeed together, wrap them in one database transaction.
- Prefer Eloquent model creation paths that keep observers/events active unless there is a deliberate performance reason to bypass them.

@verbatim
<code-snippet name="Explicit Model Construction Example" lang="php">
<?php

$reverseFulfillmentOrder = new ReverseFulfillmentOrder;
$reverseFulfillmentOrder->status = OrderStatus::RAISED;
$reverseFulfillmentOrder->routing_reason = $routingReason;
$reverseFulfillmentOrder->shopReturn()->associate($shopReturn);
$reverseFulfillmentOrder->returnable()->associate($destination);
$reverseFulfillmentOrder->save();
</code-snippet>
@endverbatim

**Event-Driven Architecture (Model + Observer Pattern):**

- Prefer event-driven workflows centered on models and observers.
- Use explicit domain transition methods for lifecycle changes and fire one explicit event per transition.
- In transition methods, guard first, mutate state, `saveQuietly()`, then `fireModelEvent(...)`.
- Keep orchestration in observers (one handler per event), and chain follow-up state changes by calling the next transition method.
- Observer handlers should dispatch events/jobs or call methods that trigger further explicit events (for example `closed` handler -> `markAsArchived()`), and run after commit.
- For full event design, observable registration, transaction/after-commit semantics, and lifecycle testing patterns, activate `model-events-observers-workflows`.

**Type Safety and IDE Support:**

- Use strict parameter and return types on methods whenever possible.
- Use enums for finite domain states and routing outcomes instead of loose strings.
- Prefer explicit method contracts and shaped PHPDoc (for example collections and tuple-like array returns) where native PHP types are not expressive enough.
- Use explicit `@var` annotations where needed to help static analysis understand collection item types.
- Keep method names intention-revealing and domain-specific so behavior is obvious at call sites.

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

### Integration Boundaries and Fakes

- Treat any external system (ERP, courier, payment provider, third-party API) as an integration boundary behind a Contract.
- In tests, prefer Laravel-style integration fakes over ad-hoc mocks for these boundaries.
- Integration fakes should be in-memory recorders that expose assertion helpers (for example `assertSent`, `assertSentCount`, `assertRouteUsed`).
- Integration fakes should support seeded responses/exceptions so test flows stay deterministic.
- Use fake-by-default for feature tests so real integration calls are not made accidentally.
- Keep app code unaware of fake vs real implementations (resolve contracts or facades backed by contracts).
- If a facade entrypoint exists for an integration boundary, it should support `fake()` and `restore()` for tests.
- For full integration-fake implementation patterns and templates, activate `laravel-integration-fakes`.

### Configuration

- No hardcoded values
- Use config files for domain settings
- Use environment variables only for infrastructure concerns
- Never call env() outside config files

### Documentation and Flowcharts

- Documentation is part of delivery. If behavior or workflow changes, update the relevant process documentation in the same change.
- Process docs should include Mermaid top-down flowcharts using `flowchart TB` that reflect current behavior.
- Keep process doc “Key files” and “Key tests” sections aligned with the code and coverage.
- Class-level documentation is also required, especially for jobs and action classes, explaining intent and behavior.
- Internal methods should be documented when non-obvious, and method documentation should stay aligned with the class-level documentation.
- For full process documentation and flowchart conventions, activate `process-documentation-with-flowcharts`.

### Testing Standards

**Test Organization:**

- Unit tests go in `tests/Unit/`
- Feature tests test e2e and large scale application logic go in `tests/Feature/`
- Otherwise organise tests to mirror the application structure
- As per the directory structure above, Shopify Tests should go in `tests/Integrations/Shopify/`
- Use Pest PHP for testing with clear, descriptive test names
- Prefer Pest `expect()` assertions for assertions in Pest tests, and chain them where that improves readability
- Do not use `PHPUnit\Framework\Assert` / `Assert::` in Pest tests unless Pest or Laravel has no suitable equivalent
- Laravel-native assertions such as response assertions and `assertDatabaseHas()` remain appropriate when they are the correct API. E.g:

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
- Prefer factories wherever possible so tests do not become bulky model-construction scripts
- If a factory exists but the scenario is awkward, improve it with named states or `configure()` hooks instead of hand-building the model graph in the test
- Use descriptive test names that explain what is being tested
- Group related tests with `describe()` blocks when a file covers multiple behaviours or methods on the same class/workflow
- Use `beforeEach()` at the narrowest useful scope so each describe group only sets up the scenario it needs
- Follow the AAA pattern: Arrange, Act, Assert
- Keep each test focused on one behavioural concern; split large workflow/E2E assertions by responsibility
- Use dataset testing for testing multiple scenarios
- Fake integration boundaries in tests and assert what was dispatched/sent via the fake.
- Reset database state between tests using `RefreshDatabase` trait
- Functions or methods inside test classes/files are an absolute last resort; prefer reusable helpers in `tests/TestCase.php` or richer support classes in `tests/Support`
- For model lifecycle event testing strategy (isolated event tests and faked follow-up events), activate `model-events-observers-workflows`.
- For full Laravel-native integration fake patterns (contracts, facade swapping, call recorders), activate `laravel-integration-fakes`.

**Database Testing:**

- Use in-memory SQLite for faster test execution
- Seed only the data needed for specific tests
- Use database transactions to roll back changes after each test
