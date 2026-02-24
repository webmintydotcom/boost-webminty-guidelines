---
name: webminty-inertia-standards
description: Apply Webminty's Inertia.js coding standards for any task that creates, edits, reviews, refactors, or formats Laravel controllers returning Inertia responses, HandleInertiaRequests middleware, shared data, or Inertia-specific testing patterns; covers the Laravel side of Inertia.js applications.
license: MIT
compatibility: Inertia.js 2+, Laravel 11+, PHP 8.2+
metadata:
  author: Webminty
---

# Webminty Inertia Guidelines

## Overview
Apply Webminty's Inertia guidelines for projects using Inertia.js as the frontend bridge. These standards cover the **Laravel side only**: controllers, middleware, shared data, redirects, form handling, and testing. Frontend framework conventions (React, Vue, Svelte) are out of scope.

## When to Activate
- Activate this skill when working on controllers that return `Inertia::render()` responses.
- Activate this skill when editing `HandleInertiaRequests` middleware or shared data.
- Activate this skill when writing tests that use `assertInertia()`.
- Activate this skill when creating or editing Inertia-related routes.

## Scope
- In scope: Inertia controllers, `Inertia::render()`, `HandleInertiaRequests` middleware, shared props, partial reloads and prop types (`Inertia::lazy()`, `Inertia::defer()`, `Inertia::optional()`, `Inertia::always()`, `Inertia::merge()`), Inertia redirects, Inertia testing with `assertInertia()`.
- Out of scope: Core PHP/Laravel standards (see `webminty-laravel-standards`), frontend framework conventions (React, Vue, Svelte components, TypeScript, state management).

## Workflow
1. Identify the Inertia artifact (controller, middleware, shared data, route, test).
2. Read `references/webminty-inertia-guidelines.md` for detailed patterns.
3. Apply `webminty-laravel-standards` first (PHP conventions, `final`, strict types), then Inertia-specific rules.

## Core Rules (Summary)
- Controllers return `Inertia::render('PageName', [...props])`.
- Page component names use PascalCase path notation: `Tickets/Show`.
- Share common data via `HandleInertiaRequests::share()`.
- Use `Inertia::lazy()`, `Inertia::defer()`, `Inertia::optional()`, `Inertia::always()`, and `Inertia::merge()` for controlling when props are loaded.
- Redirects use standard Laravel `redirect()->route()` — Inertia handles them automatically.
- Use Form Requests for validation — Inertia handles 422 responses automatically.
- Delegate business logic to Actions, not controllers.

## Do and Don't
Do:
- Use PascalCase path notation for page names (`Tickets/Index`, `Auth/Login`).
- Use Form Requests for validation (Inertia auto-handles validation errors).
- Use `redirect()->back()` after successful form submissions.
- Use `Inertia::lazy()` for expensive data that isn't always needed.
- Share auth user and flash messages via `HandleInertiaRequests`.

Don't:
- Return JSON responses from Inertia controllers — always use `Inertia::render()` or redirects.
- Put business logic in controllers.
- Share large datasets globally — use `Inertia::lazy()` or page-specific props.
- Use `Inertia::render()` for API-only endpoints — keep API controllers separate.

## Examples
```php
// Inertia controller
final class ShowController extends Controller
{
    public function __invoke(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => TicketResource::make($ticket),
            'comments' => Inertia::lazy(
                fn () => CommentResource::collection($ticket->comments),
            ),
        ]);
    }
}
```

```php
// Inertia test
test('can view ticket', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user)
        ->get(route('tickets.show', $ticket))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Show')
            ->has('ticket')
        );
});
```

## References
- `references/webminty-inertia-guidelines.md`
