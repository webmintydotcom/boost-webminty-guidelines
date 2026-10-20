# Webminty Laravel & PHP: Naming & Directory Structure

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
