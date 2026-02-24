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
- In scope: Livewire single-file and class-based components, form objects, islands, slots, `#[Reactive]` attribute, Livewire attributes (`#[Title]`, `#[Layout]`, `#[Validate]`, `#[Computed]`, `#[Url]`, `#[Locked]`, `#[On]`), `wire:` directives (`wire:model`, `wire:click`, `wire:navigate`, `wire:ref`, `wire:transition`), `data-loading` states, Livewire navigation, Livewire testing.
- Out of scope: Core PHP/Laravel standards (see `webminty-laravel-standards`), non-Livewire frontend stacks.

## Workflow
1. Identify the Livewire artifact (single-file component, class-based component, form object, island, Blade view with `wire:` directives).
2. Read `references/webminty-livewire-guidelines.md` for detailed patterns.
3. Apply `webminty-laravel-standards` first (PHP conventions, `final`, strict types), then Livewire-specific rules.

## Core Rules (Summary)
- Prefer single-file components (`.blade.php` in `resources/views/components/`) for most components.
- Components must be `final` (class-based) or `new class extends Component` (single-file).
- Note: `declare(strict_types=1)` cannot be used in single-file components (combined PHP/Blade format) — this is the one exception to the strict types rule.
- Use `#[Title]` and `#[Layout]` attributes on full-page components.
- Use `#[Reactive]` on child component properties that should update when the parent re-renders.
- Use `#[Url]` for query string binding.
- Use `#[Locked]` to prevent client modification of sensitive properties.
- Use `#[On('event-name')]` for event listeners.
- Use `#[Computed]` for derived data.
- Use `#[Validate]` for property validation.
- Use Livewire Form objects for form state and validation.
- Use islands to isolate expensive re-renders.
- Use slots for composable component content (default and named).
- Use `wire:ref` to reference child components.
- Use `wire:transition` for enter/leave animations (View Transitions API).
- Use `data-loading` CSS classes instead of verbose `wire:loading` patterns.
- Delegate business logic to Actions, not components.
- Use `wire:navigate` for SPA-style navigation.

## Do and Don't
Do:
- Use single-file components for most new components.
- Use Form objects to encapsulate form state and validation.
- Use `$this->redirect(route('...'), navigate: true)` for SPA-style redirects.
- Use `wire:navigate` on links for SPA-style navigation.
- Use islands to isolate independently re-rendering regions.
- Use `data-loading:opacity-50` for loading states via CSS.
- Use `wire:ref` to interact with child components from a parent.
- Keep components thin — delegate to Actions.

Don't:
- Put business logic in Livewire components.
- Use inline validation rules when a Form object is appropriate.
- Skip `#[Title]` or `#[Layout]` on full-page components.
- Use `$this->emit()` (Livewire 2 syntax) — use `$this->dispatch()` instead.
- Use complex `wire:loading` setups when `data-loading` CSS classes suffice.
- Use the `⚡` emoji prefix for component filenames — disable via `make_command.emoji` config if needed.

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
- `references/webminty-livewire-guidelines.md`
