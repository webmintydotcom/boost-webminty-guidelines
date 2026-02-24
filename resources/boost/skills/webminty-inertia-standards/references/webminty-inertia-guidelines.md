# Webminty Inertia Guidelines (Reference)

## Table of Contents
- [Controllers](#controllers)
- [Shared Data](#shared-data)
- [Partial Reloads](#partial-reloads)
- [Form Handling](#form-handling)
- [Redirects](#redirects)
- [Routes](#routes)
- [Directory Structure](#directory-structure)
- [Testing](#testing)

---

## Controllers

### Basic Controller

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

final class ShowController extends Controller
{
    public function __invoke(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => TicketResource::make($ticket->load('user')),
        ]);
    }
}
```

### Index Controller with Filtering

```php
final class IndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Tickets/Index', [
            'tickets' => TicketResource::collection(
                Ticket::query()
                    ->when($request->search, fn ($q, $search) => $q->search($search))
                    ->latest()
                    ->paginate(15)
                    ->withQueryString(),
            ),
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
```

### Store Controller

```php
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

### Key Rules
- Return `Inertia::render()` for page responses with a `Response` return type
- Return `RedirectResponse` after form submissions (POST/PUT/PATCH/DELETE)
- Use API Resources to format props — never pass raw Eloquent models
- Page component names use PascalCase path notation: `Tickets/Show`, `Auth/Login`
- Delegate business logic to Actions
- Use Form Requests for validation

---

## Shared Data

### HandleInertiaRequests Middleware

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? UserResource::make($request->user())
                    : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
```

### Key Rules
- Share auth user and flash messages globally
- Use API Resources for shared data — never pass raw models
- Keep shared data minimal — only include what every page needs
- Use `Inertia::lazy()` for page-specific expensive data (see Partial Reloads)

---

## Partial Reloads

### Lazy Props

```php
return Inertia::render('Tickets/Show', [
    'ticket' => TicketResource::make($ticket),

    // Only loaded on explicit partial reload request
    'comments' => Inertia::lazy(
        fn () => CommentResource::collection($ticket->comments),
    ),

    // Always included, even on partial reloads
    'permissions' => Inertia::always(
        fn () => [
            'canEdit' => $request->user()->can('update', $ticket),
            'canDelete' => $request->user()->can('delete', $ticket),
        ],
    ),
]);
```

### When to Use
- `Inertia::lazy()` — Data that is expensive to compute and not needed on initial page load (e.g., comments, activity logs, related items). The frontend must explicitly request it.
- `Inertia::always()` — Data that must always be fresh, even during partial reloads (e.g., permissions, notification counts).
- Default (no wrapper) — Data that should load on every full page visit.

---

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

## Testing

### Page Rendering

```php
test('can view ticket', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Show')
            ->has('ticket')
        );
});
```

### Props Assertion

```php
test('ticket page has correct props', function (): void {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['title' => 'Test Ticket']);

    $this->actingAs($user)
        ->get(route('tickets.show', $ticket))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tickets/Show')
            ->has('ticket', fn (Assert $prop) => $prop
                ->where('title', 'Test Ticket')
                ->etc()
            )
        );
});
```

### Form Submission

```php
test('can create a ticket', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'title' => 'Test Ticket',
            'body' => 'This is a test ticket body.',
        ])
        ->assertRedirect(route('tickets.index'));

    expect(Ticket::where('title', 'Test Ticket')->exists())->toBeTrue();
});
```

### Validation Errors

```php
test('title is required', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'title' => '',
            'body' => 'Some body text here.',
        ])
        ->assertSessionHasErrors(['title']);
});
```

### Shared Data

```php
test('shares auth user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('auth.user')
        );
});
```
