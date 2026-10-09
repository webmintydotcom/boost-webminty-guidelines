---
name: webminty-livewire-standards
description: Apply Webminty's Livewire coding standards for any task that creates, edits, reviews, refactors, or formats Livewire components (single-file, multi-file, or class-based), Livewire form objects, islands, slots, or Blade templates using wire: directives; use for full-page components, nested components, form handling, event listeners, and Livewire-specific testing patterns.
license: MIT
compatibility: Livewire 4+, Laravel 11+, PHP 8.2+
metadata:
  author: Webminty
---

# Webminty Livewire Guidelines

## Overview
Apply Webminty's Livewire 4 guidelines for projects using Livewire as the frontend stack. These standards cover component formats (single-file, multi-file, class-based), islands, slots, form objects, navigation, attributes, testing, and file organization.

## When to Activate
- Activate this skill when working on Livewire components, form objects, or Blade templates that use `wire:` directives.
- Activate this skill when creating or editing `.blade.php` single-file components in `resources/views/components/`, class-based components in `app/Livewire/`, or multi-file component directories.
- Activate this skill when writing tests that use `Livewire::test()`.

## Scope
- In scope: Livewire single-file and class-based components, form objects, islands, slots, `#[Reactive]` attribute, `#[Modelable]` attribute, `#[Defer]` attribute, `#[Async]` attribute, Livewire attributes (`#[Title]`, `#[Layout]`, `#[Validate]`, `#[Computed]`, `#[Url]`, `#[Locked]`, `#[On]`), `wire:` directives (`wire:model`, `wire:click`, `wire:navigate`, `wire:ref`, `wire:transition`, `wire:confirm`, `wire:dirty`, `wire:offline`, `wire:sort`, `wire:intersect`), `data-loading` states, Livewire navigation, `Route::livewire()`, Livewire testing.
- Out of scope: Core PHP/Laravel standards (see `webminty-laravel-standards`), Tailwind CSS conventions (see `webminty-tailwind-standards`), non-Livewire frontend stacks.

## Workflow
1. Identify the Livewire artifact (single-file component, class-based component, form object, island, Blade view with `wire:` directives).
2. Read only the reference file(s) listed under References that match the task.
3. Apply `webminty-laravel-standards` first (PHP conventions, `final`, strict types), then Livewire-specific rules.

## Core Rules
- Prefer single-file components (`.blade.php` in `resources/views/components/`) for most components.
- Components must be `final` (class-based) or `new class extends Component` (single-file).
- Note: `declare(strict_types=1)` cannot be used in single-file components (combined PHP/Blade format) — this is the one exception to the strict types rule.
- Use `#[Title]` and `#[Layout]` attributes on every full-page component.
- Use `#[Reactive]` on child component properties that should update when the parent re-renders.
- Use `#[Modelable]` on child component properties for two-way parent-child binding via `wire:model`.
- Use `#[Url]` for query string binding.
- Use `#[Locked]` to prevent client modification of sensitive properties.
- Use `#[On('event-name')]` for event listeners, and `$this->dispatch()` to fire them.
- Use `#[Computed]` for derived data.
- Use `#[Defer]` for deferred component loading and `#[Async]` for non-blocking actions.
- Use `#[Validate]` for property validation, and Livewire Form objects for form state and validation (prefer them over inline validation rules).
- Use islands to isolate independently re-rendering regions.
- Use slots for composable component content (default and named).
- Use `wire:ref` to reference child components, `wire:sort` for drag-and-drop sorting, and `wire:transition` for enter/leave animations (View Transitions API).
- Use `data-loading` CSS classes (`data-loading:opacity-50`) instead of verbose `wire:loading` patterns.
- Use `wire:navigate` on links and `$this->redirect(route('...'), navigate: true)` for SPA-style navigation.
- Use `Route::livewire()` for Livewire page routes.
- Keep components thin: delegate business logic to Actions, not components.

## Avoid
- `$this->emit()` (Livewire 2 syntax) — use `$this->dispatch()`.
- The `⚡` emoji prefix for component filenames — disable via `make_command.emoji` config if needed.

## Examples
```php
// Single-file component (resources/views/components/dashboard.blade.php)
<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] #[Layout('components.layouts.app')]
class extends Component {
    //
};
?>

<div>
    <h1>{{ __('dashboard.title') }}</h1>
</div>
```

```php
// Form object
final class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';
}
```

```php
// Livewire test
test('can render ticket list', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TicketList::class)
        ->assertOk();
});
```

## References
Read only what the task needs, all under `references/`:
- `components-attributes.md` — Component formats and `#[...]` attributes
- `islands-slots-forms.md` — Islands, slots, form objects, navigation
- `blade-structure.md` — Blade integration, naming, directory structure
- `testing.md` — Livewire testing and architecture tests
