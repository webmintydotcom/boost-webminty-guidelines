---
name: webminty-laravel-standards
description: Apply Webminty's Laravel and PHP coding standards for any task that creates, edits, reviews, refactors, or formats Laravel/PHP code or Blade templates; use for controllers, Eloquent models, routes, config, validation, migrations, tests, actions, jobs, DTOs, and related files to align with Laravel conventions, PSR-12, and Webminty project patterns.
license: MIT
metadata:
  author: Webminty
---

# Webminty Laravel & PHP Guidelines

## Overview
Apply Webminty's Laravel and PHP guidelines to keep code style consistent and Laravel-native. These standards cover PHP conventions, Laravel architecture patterns, testing with Pest, and project structure.

## When to Activate
- Activate this skill for any Laravel or PHP coding work, even if the user does not explicitly mention Webminty.
- Activate this skill when asked to generate, edit, format, refactor, review, or align Laravel/PHP code.
- Activate this skill when working on `.php` or `.blade.php` files, routes, controllers, models, config, validation, migrations, tests, actions, jobs, or Data objects.

## Scope
- In scope: `.php`, `.blade.php`, Laravel conventions (routes, controllers, config, validation, migrations, tests, actions, jobs, DTOs, enums, commands, API).
- Out of scope: JS/TS, CSS, infrastructure, database schema design, non-Laravel frameworks, frontend-stack-specific patterns (see `webminty-livewire-standards` or `webminty-inertia-standards` skills).

## Workflow
1. Identify the artifact (action, controller, model, Blade, test, job, DTO, enum, route, migration, etc.).
2. Read `references/webminty-laravel-guidelines.md` and focus on the relevant sections.
3. Apply the core Laravel principle first, then PHP standards, then section-specific rules.
4. If a rule conflicts with existing project conventions, follow Laravel conventions and keep changes consistent.

## Core Rules (Summary)
- Follow Laravel conventions first.
- Follow PSR-12 (includes PSR-1).
- Every PHP file must have `declare(strict_types=1)`.
- All classes should be `final` by default.
- Use typed properties and explicit return types (including `void`).
- Use constructor property promotion when possible.
- Prefer early returns and avoid `else` when possible.
- Always use curly braces for control structures.
- Use string interpolation over concatenation.
- Use strict comparison (`===`/`!==`).
- Use `$guarded = ['id']` instead of `$fillable`.
- Use the `casts()` method instead of the `$casts` property.
- Use the `#[Scope]` attribute for query scopes (Laravel 11+).
- Actions are `final` classes with a single `execute()` method.
- DTOs extend `Spatie\LaravelData\Data` and are `final`.
- Use Pest PHP for all tests with `test()` function syntax.
- Use array notation for validation rules (not pipe syntax).

## Do and Don't
Do:
- Use kebab-case URLs, dot-notation route names, and snake_case database columns.
- Use array notation for validation rules.
- Use `config()` and avoid `env()` outside config files.
- Use anonymous migrations (return new class).
- Use `hash_id` pattern for public-facing IDs.
- Prefix boolean columns with `is_` or `has_`.
- Use Form Requests for controller validation.
- Delegate business logic to Actions from controllers.
- Use architecture tests to enforce coding standards.

Don't:
- Use `$fillable` instead of `$guarded`.
- Use the `$casts` property instead of the `casts()` method.
- Use `scopeX()` prefix instead of the `#[Scope]` attribute.
- Put business logic in controllers.
- Use `env()` outside config files.
- Use pipe syntax for validation rules.
- Skip `declare(strict_types=1)`.
- Skip `final` on classes.
- Use `dd()`, `dump()`, `ray()`, or `var_dump()` in committed code.

## Examples
```php
// Action
final class CreateTicket
{
    public function execute(TicketData $data): Ticket
    {
        return Ticket::create($data->toArray());
    }
}
```

```php
// Model
final class Ticket extends Model
{
    use HasFactory;
    use HasHashIds;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'due_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
```

```php
// Pest Test
test('can create a ticket', function (): void {
    $user = User::factory()->create();
    $data = new TicketData(title: 'Test', body: 'Body', user_id: $user->id);
    $ticket = app(CreateTicket::class)->execute($data);

    expect($ticket)
        ->toBeInstanceOf(Ticket::class)
        ->title->toBe('Test');
});
```

```blade
@if($condition)
    <x-ui.card>
        {{ $slot }}
    </x-ui.card>
@endif
```

## References
- `references/webminty-laravel-guidelines.md`
