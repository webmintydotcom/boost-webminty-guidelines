# Webminty Laravel & PHP Guidelines (Reference)

## Table of Contents
- [Core Laravel Principle](#core-laravel-principle)
- [PHP Standards](#php-standards)
- [Naming Conventions](#naming-conventions)
- [Directory Structure](#directory-structure)
- [Models](#models)
- [Eloquent](#eloquent)
- [Database & Migrations](#database--migrations)
- [Actions](#actions)
- [Controllers](#controllers)
- [Routes](#routes)
- [Blade Templates](#blade-templates)
- [Jobs](#jobs)
- [Commands](#commands)
- [API Standards](#api-standards)
- [Batches vs Pipelines](#batches-vs-pipelines)
- [General Laravel](#general-laravel)
- [Linting & Code Quality](#linting--code-quality)
- [Testing](#testing)
- [Quick Reference](#quick-reference)

---

## Core Laravel Principle

**Follow Laravel conventions first.** If Laravel has a documented way to do something, use it. Only deviate when you have a clear justification.

---

## PHP Standards

### Strict Types
Every PHP file must declare strict types:

```php
<?php

declare(strict_types=1);

namespace App\Actions\Tickets;
```

### Type Hints
- All method parameters must have type hints
- All methods must declare return types (including `void`)
- Use union types for flexible parameters: `int|string|Item`
- Use `?Type` for nullable types

```php
public function execute(TicketData $data): Ticket
public function delete(int $id): void
public function find(string $hashId): ?User
public function execute(int|string|Item $item): void
```

### Final Classes
Classes should be `final` by default:

```php
final class CreateTicket
{
    public function execute(TicketData $data): Ticket
    {
        return Ticket::create($data->toArray());
    }
}
```

### Visibility
Always declare explicit visibility on properties and methods.

### Constructor Property Promotion
Use constructor promotion for DTOs and simple classes:

```php
final class TicketData extends Data
{
    public function __construct(
        public string $title,
        public string $body,
        public int $user_id,
        public ?int $category_id = null,
    ) {}
}
```

### Enums
Use backed enums with explicit values and PascalCase cases:

```php
enum Status: int
{
    case Pending = 0;
    case Active = 1;
    case Completed = 2;
}

enum WeightTypes: string
{
    case Kilograms = 'kg';
    case Pounds = 'lbs';
}
```

### PHP 8+ Features
- **Match expressions** over switch statements
- **Arrow functions** for simple callbacks: `fn (User $user) => $user->name`
- **Named arguments** for many parameters
- **Null coalescing**: `$name = $user->name ?? 'Guest'`
- **Null coalescing assignment**: `$this->cache ??= new Cache()`

### Comparisons
Always use strict comparison (`===`/`!==`):

```php
if ($status === Status::Active) { }
if ($count === 0) { }
if ($name !== null) { }
```

### Imports
- Order alphabetically, grouped by type
- No unused imports
- No aliases unless name conflicts exist

### Formatting
- Short array syntax: `['one', 'two']`
- `new User` without parentheses when no arguments (per Pint config)
- Trailing commas in multi-line arrays and parameters

---

## Naming Conventions

### Laravel Naming Table

| What | How | Good | Bad |
|------|-----|------|-----|
| Blade | kebab-case | `partials.top-header` | `top_header` |
| Collections | Plural, camelCase | `activeUsers` | `active_users` |
| Commands | kebab-case | `app:send-email` | `SendEmail` |
| Config | snake_case | `google_calendar.php` | `google-calendar.php` |
| Controllers | Singular | `UserController` | `UsersController` |
| Methods | camelCase | `getUsers` | `get_users` |
| Models | Singular PascalCase | `User` | `Users` |
| Route Names | dot notation | `tickets.show` | `tickets-show` |
| Routes | Plural | `articles/1` | `article/1` |
| Tables | Plural snake_case | `users` | `User` |
| URLs | kebab-case | `/about-us` | `/about_us` |
| Variables | camelCase | `$userName` | `$user_name` |
| Views | kebab-case | `show-user.blade.php` | `show_user.blade.php` |

### Class Naming
- **Models**: PascalCase, singular (`User`, `Ticket`, `MashCategory`)
- **Actions**: PascalCase, verb-first (`CreateTicket`, `ConvertKilogramsToPounds`)
- **DTOs**: PascalCase, suffixed with `Data` (`TicketData`, `PlanData`)
- **Enums**: PascalCase, singular (`Status`, `WeightTypes`)
- **Traits**: PascalCase, prefixed with `Has` (`HasHashIds`, `HasActiveInactive`)

### Method Naming
- General methods: camelCase, verb-first (`execute`, `authenticate`, `generateSlug`)
- Query scopes: use `#[Scope]` attribute (preferred over legacy `scopeX()` prefix)
- Relationships: singular for belongsTo/hasOne, plural for hasMany/belongsToMany

### Database Naming
- Tables: snake_case, plural (`users`, `mash_items`)
- Columns: snake_case (`user_id`, `is_active`, `created_at`)
- Booleans: prefix with `is_` or `has_` (`is_active`, `has_subscription`)
- Foreign keys: `{model}_id` (`user_id`, `category_id`)
- Timestamps: `_at` suffix (`due_at`, `published_at`)

### File Naming
- PHP files: match class name exactly (`CreateTicket.php`)
- Blade views: kebab-case (`ticket-list.blade.php`)
- Migrations: Laravel default format (`{timestamp}_create_{table}_table.php`)

---

## Directory Structure

```
app/
├── Actions/              # Business logic action classes
├── Data/                 # Spatie LaravelData DTOs
├── Enums/                # PHP 8.1+ Enums
├── Http/
│   ├── Controllers/      # HTTP controllers
│   ├── Middleware/        # Custom middleware
│   └── Requests/         # Form request validation
├── Models/
│   └── Traits/           # Reusable model traits
├── Observers/            # Model lifecycle observers
├── Providers/            # Service providers
├── View/
│   └── Components/       # Blade component classes
└── ViewData/             # View-specific data classes (optional)

database/
├── factories/            # Model factories for testing
├── migrations/           # Database migrations
└── seeders/              # Database seeders

resources/views/
├── components/           # Anonymous Blade components
├── layouts/              # Layout templates
├── pages/                # Page views
└── partials/             # Reusable partials

routes/
├── web.php               # Web routes
├── auth.php              # Authentication routes
└── console.php           # Artisan commands

tests/
├── Feature/
│   ├── Actions/          # Action tests (mirrors app/Actions/)
│   ├── Models/           # Model tests
│   └── Auth/             # Authentication tests
├── Unit/
│   └── ArchitectureTest.php
├── Pest.php              # Pest configuration
└── TestCase.php          # Base test case
```

Create subdirectories when more than 5-7 related files exist or when domain boundaries are clear.

---

## Models

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasHashIds;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Ticket extends Model
{
    use HasFactory;
    use HasHashIds;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'due_at' => 'datetime',
            'settings' => 'array',
            'status' => Status::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketItem::class)
            ->orderBy('position');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
```

### Key Rules
- Use `$guarded = ['id']` instead of `$fillable`
- Use the `casts()` method (not `$casts` property)
- Use `#[Scope]` attribute for query scopes (Laravel 11+)
- Always type relationship return values (`BelongsTo`, `HasMany`, etc.)
- Use one trait per line
- Singular names for belongsTo/hasOne, plural for hasMany/belongsToMany

### Common Cast Types
- `boolean` for `is_*` and `has_*` columns
- `datetime` for timestamp columns
- `array` for JSON columns
- `decimal:2` for money/precise decimals
- `Enum::class` for enum columns
- `Data::class` for Spatie LaravelData columns

### HasHashIds Trait
Generate URL-safe public IDs:

```php
trait HasHashIds
{
    protected static function boot(): void
    {
        parent::boot();

        static::created(function ($model): void {
            $reflect = new ReflectionClass(self::class);
            $connection = Str::lower($reflect->getShortName());

            if ($model->hash_id) {
                return;
            }

            $model->hash_id = Hashids::connection($connection)
                ->encode((string) $model->id);
            $model->save();
        });
    }
}
```

### Accessors and Mutators
Use `Attribute::make()` syntax:

```php
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}",
    );
}
```

### Model Safety (AppServiceProvider)

```php
public function boot(): void
{
    Model::preventLazyLoading();

    if ($this->app->isProduction()) {
        Model::handleLazyLoadingViolationUsing(
            function ($model, $relation): void {
                info("Attempted to lazy load [{$relation}] on model [{$model}].");
            }
        );
        DB::prohibitDestructiveCommands();
    } else {
        Model::shouldBeStrict();
    }
}
```

---

## Eloquent

### Query Builder
Always start queries with `query()` for clarity:

```php
$tickets = Ticket::query()
    ->with(['user', 'category'])
    ->where('status', Status::Open)
    ->latest()
    ->paginate(15);
```

### Query Scopes

```php
// Using #[Scope] attribute (preferred)
#[Scope]
protected function active(Builder $query): void
{
    $query->where('is_active', true);
}

#[Scope]
protected function forUser(Builder $query, User $user): void
{
    $query->where('user_id', $user->id);
}

// Usage
Ticket::active()->forUser($user)->get();
```

### Custom Query Builders
For models with 3+ scopes:

```php
final class UserBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function search(string $term): self
    {
        return $this->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
```

### Eager Loading
Always eager load to prevent N+1:

```php
$tickets = Ticket::with(['user', 'category', 'items'])->get();

// Constrained
$tickets = Ticket::with([
    'comments' => fn ($query) => $query->latest()->limit(5),
])->get();
```

### Transactions

```php
DB::transaction(function () use ($data): Order {
    $order = Order::create($data);
    $order->items()->createMany($data['items']);
    return $order;
});
```

### Large Datasets
- Use `chunk()` or `chunkById()` for processing
- Use `lazy()` for memory-efficient iteration
- Use `cursor()` for read-only operations

---

## Database & Migrations

### Migration Structure

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('hash_id')->nullable()->default(null)->unique();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable()->default(null);
            $table->timestampTz('due_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
```

### Column Conventions

| Type | Convention | Examples |
|------|------------|----------|
| Primary Key | `id` | `id` |
| Hash ID | `hash_id` | `hash_id` |
| Foreign Key | `{model}_id` | `user_id`, `category_id` |
| Boolean | `is_` or `has_` prefix | `is_active`, `has_subscription` |
| Timestamps | `_at` suffix | `due_at`, `published_at` |
| JSON | nullable with default null | `settings`, `metadata` |

### Foreign Keys

```php
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->foreignId('category_id')->nullable()->constrained();
$table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
```

### Indexes

```php
$table->index('is_active');
$table->index(['user_id', 'is_active']);
$table->unique('email');
$table->unique(['name', 'user_id']);
```

### Hash ID Pattern

```php
$table->string('hash_id')->nullable()->default(null)->unique();
```

---

## Actions

### Overview
Actions are single-purpose `final` classes that encapsulate business logic with a single `execute()` method.

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Data\TicketData;
use App\Models\Ticket;

final class CreateTicket
{
    public function execute(TicketData $data): Ticket
    {
        return Ticket::create($data->toArray());
    }
}
```

### Key Rules
- Single responsibility: one action per class
- Use DTOs for complex parameters
- Return the created model, `void` for updates/deletes, or the computed result
- Verb-first naming: `CreateTicket`, `UpdateItem`, `ConvertKilogramsToPounds`
- Use constructor injection for dependencies
- Organize in subdirectories by domain: `app/Actions/Tickets/`

### Calling Actions

```php
// Via container
$ticket = app(CreateTicket::class)->execute($data);

// Via dependency injection
public function store(CreateTicket $createTicket, TicketData $data): Response
{
    $ticket = $createTicket->execute($data);
    return redirect()->route('tickets.show', $ticket);
}
```

### Complex Actions

```php
final class ProcessOrder
{
    public function __construct(
        private readonly ValidateInventory $validateInventory,
        private readonly CreatePayment $createPayment,
        private readonly SendConfirmation $sendConfirmation,
    ) {}

    public function execute(OrderData $data): Order
    {
        $this->validateInventory->execute($data->items);
        $order = Order::create($data->toArray());
        $this->createPayment->execute($order);
        $this->sendConfirmation->execute($order);
        return $order;
    }
}
```

### When to Create an Action
- Logic is used in multiple places
- Logic requires more than a few lines
- Logic has business rules or validation
- Logic has side effects (events, notifications, cache)
- Logic needs independent testing

---

## Controllers

### Responsibilities
Controllers should be thin:
- Handle HTTP requests and return HTTP responses
- Validate input via Form Requests
- Authorize actions via policies or gates
- Delegate business logic to Actions

### Single Action Controllers (Preferred)

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tickets;

use App\Actions\Tickets\CreateTicket;
use App\Data\TicketData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\StoreTicketRequest;
use Illuminate\Http\RedirectResponse;

final class StoreController extends Controller
{
    public function __invoke(
        StoreTicketRequest $request,
        CreateTicket $createTicket,
    ): RedirectResponse {
        $ticket = $createTicket->execute(TicketData::from($request));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', __('tickets.created'));
    }
}
```

### Parameter Order
1. Route model bindings
2. Form Request
3. Injected dependencies

### Form Requests
Always use Form Requests for validation with array syntax:

```php
final class StoreTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
```

### File Organization

```
app/Http/Controllers/
├── Controller.php
├── DashboardController.php
├── Tickets/
│   ├── IndexController.php
│   ├── StoreController.php
│   └── ShowController.php
└── Api/
    └── Tickets/
        └── IndexController.php
```

---

## Routes

### Basic Structure

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/tickets', TicketIndexController::class)->name('tickets.index');
});

require __DIR__ . '/auth.php';
```

### Naming
- Route names: dot notation (`tickets.show`, `tickets.index`)
- URLs: kebab-case (`/about-us`, `/user-profile`)
- Always name routes for maintainability

### RESTful Naming

| Verb | URI | Route Name |
|------|-----|------------|
| GET | /tickets | tickets.index |
| GET | /tickets/create | tickets.create |
| POST | /tickets | tickets.store |
| GET | /tickets/{ticket} | tickets.show |
| PUT/PATCH | /tickets/{ticket} | tickets.update |
| DELETE | /tickets/{ticket} | tickets.destroy |

### Route Model Binding

```php
Route::get('/tickets/{ticket:hash_id}', TicketShowController::class);
```

### Key Rules
- Use single-action controllers or resource controllers for route targets
- Group routes by middleware
- Separate auth routes into `routes/auth.php`
- Avoid logic in route files

---

## Blade Templates

### Naming
- Use kebab-case for file names: `ticket-list.blade.php`
- Use kebab-case for component names: `<x-ui.button-group>`

### Components
Prefer `x-` component syntax over `@include`:

```php
<x-ui.card>
    <x-slot:header>Card Title</x-slot:header>
    Card content here
</x-ui.card>
```

### Data Handling
- All data should come from controllers
- No Eloquent queries in views
- Simple formatting is acceptable: `{{ $date->format('M j, Y') }}`

### Translations
Always use `__()` translation helper:

```php
<h1>{{ __('tickets.index.title') }}</h1>
```

### CSS
Use Tailwind utility classes. Extract repeated patterns to components.

### JavaScript
Use Alpine.js for simple interactivity. Prefer external files for larger scripts.

### File Organization

```
resources/views/
├── components/
│   ├── layouts/
│   ├── ui/
│   └── forms/
├── pages/
├── partials/
└── emails/
```

---

## Jobs

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessOrder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        public Order $order,
    ) {}

    public function handle(PaymentService $payment): void
    {
        $payment->charge($this->order);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Order processing failed', [
            'order_id' => $this->order->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
```

### Key Rules
- Jobs must be `final` and implement `ShouldQueue`
- Use one trait per line
- Inject dependencies in `handle()`, not constructor
- Store minimal data (models serialize to IDs)
- Keep jobs small and focused
- Always handle failures with `failed()` method
- Use middleware for rate limiting and preventing overlaps

### Dispatching

```php
ProcessOrder::dispatch($order);
ProcessOrder::dispatch($order)->delay(now()->addMinutes(5));
ProcessOrder::dispatch($order)->onQueue('orders');
```

### Job Chains

```php
Bus::chain([
    new ProcessPayment($order),
    new UpdateInventory($order),
    new SendConfirmation($order),
])->dispatch();
```

---

## Commands

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class CleanupInactiveUsers extends Command
{
    protected $signature = 'app:cleanup-inactive-users
                            {--days=30 : Days of inactivity threshold}
                            {--dry-run : Run without making changes}';

    protected $description = 'Deactivate users who have been inactive';

    public function handle(DeactivateInactiveUsers $deactivateUsers): int
    {
        $count = $deactivateUsers->execute(
            days: (int) $this->option('days'),
            dryRun: (bool) $this->option('dry-run'),
        );

        $this->info("{$count} users processed.");

        return Command::SUCCESS;
    }
}
```

### Key Rules
- Use kebab-case for command signatures prefixed with `app:`
- Inject dependencies in `handle()`, not constructor
- Delegate business logic to Actions
- Always provide descriptions
- Support dry-run for destructive commands
- Return `Command::SUCCESS` or `Command::FAILURE`

### Scheduling

```php
Schedule::command('app:cleanup-inactive-users')
    ->daily()
    ->at('03:00')
    ->withoutOverlapping()
    ->onOneServer();
```

---

## API Standards

### Versioning
Include version in URL:

```php
Route::prefix('api/v1')->group(function () { ... });
```

- Always version APIs from the start (`v1`), even for internal APIs.
- Use URL-based versioning (`/api/v1/...`) — not header-based versioning.
- When introducing breaking changes, create a new version (`v2`) while keeping the previous version supported during a deprecation period.
- Keep controller namespaces organized by version: `App\Http\Controllers\Api\V1\`, `App\Http\Controllers\Api\V2\`.
- Route files can use separate groups or files per version for clarity.

```
app/Http/Controllers/Api/
├── V1/
│   ├── TicketController.php
│   └── UserController.php
└── V2/
    ├── TicketController.php
    └── UserController.php
```

### Naming
- Plural nouns for resource names: `/api/v1/tickets`
- No verbs in URLs

### HTTP Methods

| Method | Purpose |
|--------|---------|
| GET | Retrieve resource(s) |
| POST | Create a resource |
| PUT | Replace entire resource |
| PATCH | Partial update |
| DELETE | Remove a resource |

### Response Format
Use Laravel API Resources:

```php
final class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hash_id,
            'title' => $this->title,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

### Authentication
Use Laravel Sanctum for API authentication.

### Rate Limiting
Apply rate limits liberally:
- Always on auth endpoints and email-sending endpoints
- Always on POST requests and expensive operations
- Recommended on all public/unauthenticated endpoints

### Key Rules
- Always return JSON
- Use `hash_id` in URLs, not internal IDs
- Never expose sensitive data
- Use Form Requests for validation
- Use proper HTTP status codes (201 for created, 204 for no content, 422 for validation)

---

## Batches vs Pipelines

### When to Use Each

| Scenario | Pipeline | Batch |
|----------|----------|-------|
| Simple data transformation | Yes | No |
| Single value passed between steps | Yes | No |
| Multiple parameters needed | No | Yes |
| Conditional logic between steps | No | Yes |
| Different error handling per step | No | Yes |
| Linear A -> B -> C flow | Yes | No |

### Pipeline Example

```php
app(Pipeline::class)
    ->send($feet)
    ->through([
        ConvertFeetToInches::class,
        ConvertInchesToCentimeters::class,
        ConvertCentimetersToMeters::class,
    ])
    ->thenReturn();
```

### Batch Example

```php
final class CreateUserBatch
{
    public function handle(UserData $data, array $slackChannels): User
    {
        $user = $this->createUser->execute($data);
        $this->setupGitHub($user);
        $this->setupSlack($user, $slackChannels);
        $this->sendWelcomeEmail->execute($user);
        return $user;
    }
}
```

### File Organization

```
app/
├── Batches/           # Batch orchestrators
├── Pipes/             # Pipeline step classes
└── Actions/           # Reusable single actions
```

---

## General Laravel

### Dependency Injection
- Prefer constructor/method injection
- Use `app()` only when DI isn't possible
- Never inject the container itself

### Helpers vs Facades
- Use helper functions for simple get/set: `session()`, `config()`, `cache()`, `auth()`
- Use facades for chained methods: `Cache::tags()->put()`, `Log::channel()`

### Strings and Arrays
- Use `Str::` helpers over PHP string functions
- Use `Arr::` helpers for array manipulation
- Use collections over array functions

### Configuration
- Use `config()` everywhere, never `env()` outside config files
- Type-cast env values in config files

### Logging
- Use appropriate levels (debug, info, warning, error, critical)
- Always include structured context arrays
- Use channels for specific destinations

### Events

```php
final class OrderPlaced
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Order $order,
    ) {}
}
```

### Custom Exceptions

```php
final class InsufficientFundsException extends Exception
{
    public function __construct(
        public readonly float $available,
        public readonly float $required,
    ) {
        parent::__construct("Insufficient funds: {$available} available, {$required} required");
    }
}
```

---

## Linting & Code Quality

### Tools

| Tool | Purpose |
|------|---------|
| Laravel Pint | Code formatting (PHP CS Fixer) |
| PHPStan + Larastan | Static analysis (level 5) |
| Rector | Automated refactoring |

### Key Pint Rules
- `declare_strict_types`: Adds `declare(strict_types=1)`
- `final_class`: Makes classes `final`
- `strict_comparison`: Uses `===` instead of `==`
- `void_return`: Adds `void` return types
- `new_with_parentheses: false`: `new User` instead of `new User()`
- `trailing_comma_in_multiline`: Trailing commas
- `ordered_imports`: Alphabetical imports

### Composer Scripts

```json
{
    "scripts": {
        "lint": "./vendor/bin/pint",
        "lint:check": "./vendor/bin/pint --test",
        "analyse": "./vendor/bin/phpstan analyse",
        "test": "./vendor/bin/pest",
        "quality": ["@lint:check", "@analyse", "@test"]
    }
}
```

---

## Testing

### Framework
All projects use **Pest PHP**.

### Run Tests

- Always run tests with the `--parallel` flag: `php artisan test --compact --parallel`
- When filtering tests, also use parallel: `php artisan test --compact --parallel --filter=testName`

### Pest Configuration

```php
pest()->extends(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
```

### Test Syntax
Always use `test()` function (not `it()` or class-based):

```php
test('can create a ticket', function (): void {
    $user = User::factory()->create();
    $data = new TicketData(
        title: 'Test Ticket',
        body: 'This is a test ticket.',
        user_id: $user->id,
    );

    $ticket = app(CreateTicket::class)->execute($data);

    expect($ticket)
        ->toBeInstanceOf(Ticket::class)
        ->title->toBe('Test Ticket');
});
```

### Architecture Tests

```php
arch()
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump', 'ray', 'var_dump', 'print_r']);

arch()
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->toHaveMethod('casts');

arch()
    ->expect('App\Actions')
    ->toHaveMethod('execute')
    ->toBeFinal();

arch()
    ->expect('App\Data')
    ->toExtend('Spatie\LaravelData\Data')
    ->toBeFinal();

arch()
    ->expect('App\Http\Controllers')
    ->toBeFinal();

arch()
    ->expect('App\Enums')
    ->toBeEnums();

arch()
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue')
    ->toBeFinal();

arch()
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->toBeFinal();

arch()->preset()->php();
arch()->preset()->security()->ignoring('md5');
arch()->preset()->laravel();
```

### Testing Actions

```php
test('can create a ticket with all fields', function (): void {
    $user = User::factory()->create();
    $data = new TicketData(
        title: 'Test Ticket',
        body: 'Test body.',
        user_id: $user->id,
    );

    $ticket = app(CreateTicket::class)->execute($data);

    expect($ticket)
        ->toBeInstanceOf(Ticket::class)
        ->title->toBe('Test Ticket');
});
```

### Testing HTTP

```php
test('can view dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});
```

### Factory Best Practices

```php
final class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'is_active' => true,
            'user_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
```

---

## Quick Reference

### Must-Have in Every PHP File
- `declare(strict_types=1)`
- `final` class declaration
- Explicit return types on all methods
- Type hints on all parameters

### Class Conventions
- Actions: `final class VerbNoun { public function execute(): ... }`
- Models: `final class Noun extends Model` with `$guarded = ['id']` and `casts()` method
- DTOs: `final class NounData extends Data`
- Jobs: `final class VerbNoun implements ShouldQueue`
- Commands: `final class VerbNoun extends Command` with `app:kebab-case` signature

### Validation
- Always use array syntax: `['required', 'string', 'max:255']`
- Use Form Requests for controllers

### Database
- `$guarded = ['id']` not `$fillable`
- `casts()` method not `$casts` property
- `#[Scope]` attribute not `scopeX()` methods
- `hash_id` pattern for public-facing IDs
- Boolean columns: `is_` or `has_` prefix
- Anonymous migrations: `return new class extends Migration`

### Code Quality
- Use `===`/`!==` (strict comparison)
- Prefer early returns over nested if/else
- Use string interpolation over concatenation
- No `dd()`, `dump()`, `ray()`, `var_dump()` in committed code
- Run `composer quality` (Pint + PHPStan + Pest) before committing
