# Webminty Inertia: Forms, Redirects, Routes, Structure & SSR

## Form Handling

### Server-Side Pattern
Inertia handles validation errors automatically. When a Form Request fails, Laravel returns a 422 response, and Inertia makes the errors available on the frontend.

```php
// Controller — no special Inertia handling needed for validation
final class StoreController extends Controller
{
    public function __invoke(
        StoreTicketRequest $request,
        CreateTicket $createTicket,
    ): RedirectResponse {
        $createTicket->execute(TicketData::from($request));

        return redirect()
            ->route('tickets.index')
            ->with('success', __('tickets.created'));
    }
}
```

```php
// Form Request — standard Laravel, Inertia handles the rest
final class StoreTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
```

### Key Rules
- Use Form Requests for validation — Inertia handles 422 responses automatically
- Use `redirect()->back()` or `redirect()->route()` after successful submissions
- Flash success/error messages via `->with('success', '...')`
- No need for special error formatting — Inertia maps Laravel validation errors to the frontend

---

---

## Redirects

### After Form Submission

```php
// Redirect to a specific route
return redirect()->route('tickets.show', $ticket);

// Redirect back (common for updates/deletes)
return redirect()->back()->with('success', __('tickets.updated'));

// Redirect with flash message
return redirect()
    ->route('tickets.index')
    ->with('success', __('tickets.created'));
```

### External Redirects

```php
// For redirects to external URLs
return Inertia::location($externalUrl);
```

### Key Rules
- Use standard Laravel redirects — Inertia intercepts and handles them as SPA navigations
- Use `Inertia::location()` only for external URLs or full page reloads
- Flash messages are shared via `HandleInertiaRequests` middleware

---

---

## Routes

### Basic Structure

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/tickets', Tickets\IndexController::class)->name('tickets.index');
    Route::get('/tickets/create', Tickets\CreateController::class)->name('tickets.create');
    Route::post('/tickets', Tickets\StoreController::class)->name('tickets.store');
    Route::get('/tickets/{ticket:hash_id}', Tickets\ShowController::class)->name('tickets.show');
    Route::put('/tickets/{ticket:hash_id}', Tickets\UpdateController::class)->name('tickets.update');
    Route::delete('/tickets/{ticket:hash_id}', Tickets\DestroyController::class)->name('tickets.destroy');
});
```

### Key Rules
- Use single-action controllers (`__invoke`) for each route
- Use `hash_id` for route model binding on public-facing URLs
- Follow RESTful naming conventions (same as `webminty-laravel-standards`)
- GET routes render Inertia pages, POST/PUT/PATCH/DELETE routes redirect

---

---

## Directory Structure

### Laravel Side

```
app/Http/Controllers/
├── Controller.php
├── DashboardController.php
├── Tickets/
│   ├── IndexController.php
│   ├── CreateController.php
│   ├── StoreController.php
│   ├── ShowController.php
│   ├── UpdateController.php
│   └── DestroyController.php
└── Api/
    └── ...

app/Http/Middleware/
└── HandleInertiaRequests.php

app/Http/Resources/
├── TicketResource.php
└── UserResource.php
```

### Frontend Side (for reference only — conventions are out of scope)

```
resources/js/
├── pages/
│   ├── Dashboard.tsx
│   └── Tickets/
│       ├── Index.tsx
│       ├── Create.tsx
│       └── Show.tsx
├── components/
│   └── ...
└── layouts/
    └── AppLayout.tsx
```

Page component paths in `Inertia::render()` map to the `resources/js/pages/` directory.

---

---

## Server-Side Rendering (SSR)

Inertia supports server-side rendering for improved initial page load performance and SEO. SSR pre-renders pages on the server so users see content before JavaScript hydrates.

### Laravel-Side Setup

Publish the SSR configuration and enable it:

```bash
php artisan inertia:start-ssr
```

This starts a Node.js process alongside the Laravel application that renders pages on the server. The process must remain running in production (use a process manager like Supervisor or PM2).

### Key Rules
- Enable SSR in your Inertia configuration (`config/inertia.php`)
- The `php artisan inertia:start-ssr` command starts the SSR server
- SSR runs as a separate Node.js process — ensure it is managed by a process supervisor in production
- Detailed SSR configuration (bundling, framework-specific setup) is frontend-specific and out of scope for this skill

---
