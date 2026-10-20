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

### Docblocks
Avoid docblocks. Add one only when it is absolutely needed:
- The type system can't express it: generics on iterables, array shapes
- The code is genuinely confusing and the docblock explains *why* or *what it means*, not what the types already say

Rules when you do write one:
- Never repeat what the signature already says
- Always import class names; never use fully qualified names
- Prefer one line: `/** @return Collection<int, User> */`
- Give iterables key and value types
- Use array shapes for fixed keys, one key per line

```php
/** @return Collection<int, User> */
public function getUsers(): Collection

/** @return array{
    first: Ticket,
    second: Ticket
} */
public function pair(): array
```

### Visibility
Always declare explicit visibility on properties and methods.

### Constructor Property Promotion
Use constructor promotion for DTOs and simple classes:

```php
class TicketData extends Data
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
- **`readonly` classes** for immutable value objects: `readonly class Money`
- **`never`** return type for methods that always throw or exit
- **First-class callable syntax**: `array_map(strtoupper(...), $names)`
- **`#[Override]`** on methods that override a parent or interface method

### PHP 8.4 Features
Target PHP 8.4+. Use these where they simplify code:

```php
// Property hooks: replace trivial getters/setters and accessors
class Ticket
{
    public string $title {
        set => trim($value);
    }

    public string $summary {
        get => str($this->body)->limit(80)->toString();
    }
}

// Asymmetric visibility: public read, restricted write
class Counter
{
    public private(set) int $count = 0;
}

// new without wrapping parentheses when chaining (the argument parentheses stay)
$name = new Ticket()->title;

// Array helpers
$first = array_find($items, fn (Item $item) => $item->isActive());
$hasActive = array_any($items, fn (Item $item) => $item->isActive());
$allActive = array_all($items, fn (Item $item) => $item->isActive());
```

- Use `#[Deprecated]` (native) instead of `@deprecated` docblocks for new deprecations
- Do not use property hooks on Eloquent model attributes; use `casts()` and accessors (`Attribute::make()`) there
- Prefer `public private(set)` over a public property plus a getter method

### Control Flow
- Happy path last: handle error conditions first, success case at the end
- Avoid `else`: use early returns instead of nested conditions
- Always use curly braces, even for single statements
- Ternaries: one line only when short; otherwise each part on its own line

```php
if (! $user) {
    return null;
}

if (! $user->isActive()) {
    return null;
}

// Process the active user...

$name = $isFoo ? 'foo' : 'bar';

$result = $object instanceof Model
    ? $object->name
    : 'A default value';
```

### Comments
Avoid comments; write expressive code instead.
- If a comment explains *what* a block does, extract it into a well-named method
- When a comment is needed, explain *why*, not what
- Single line: `// ` with a space after the slashes
- Multi-line blocks start with a single `*`

```php
// Retry once; the payment gateway drops the first request after idle.

/*
 * Multi-line comments use a single leading asterisk
 * on each line.
 */
```

### Whitespace
- Blank lines between statements so code can breathe
- Exception: a run of equivalent single-line operations stays together
- No blank lines just inside `{}`

```php
$user = User::find($id);

$ticket = $user->tickets()->create($data);

$ticket->notify();

$first = 1;
$second = 2;
$third = 3;
```

Pint's `blank_line_before_statement` enforces part of this; the rest is judgement.

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
- `new User` without parentheses when no arguments and nothing is chained (per Pint config); when chaining, keep them: `new User()->name`
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
- **Jobs**: PascalCase, action-based, no suffix (`CreateUser`, `SendEmailNotification`)
- **Events**: PascalCase, tense-based (`UserRegistering`, `UserRegistered`)
- **Listeners**: action + `Listener` suffix (`SendInvitationMailListener`)
- **Commands**: action + `Command` suffix (`PublishScheduledPostsCommand`); the signature stays `app:kebab-case`
- **Mailables**: purpose + `Mail` suffix (`AccountActivatedMail`)
- **Resources**: plural + `Resource` suffix by default (`UsersResource`, `TicketsResource`), since one class serves `::make()` and `::collection()`; use singular when it only ever represents one thing (`StatsResource`, `CurrentUserResource`)

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
├── Enums/                # Enums
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
