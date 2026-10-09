# Webminty Laravel & PHP: PHP Standards, Naming & Structure

## Core Laravel Principle

**Follow Laravel conventions first.** If Laravel has a documented way to do something, use it. Only deviate when you have a clear justification.

---

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
