# Webminty Inertia: Controllers, Shared Data & Partial Reloads

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

---

## Shared Data

### HandleInertiaRequests Middleware

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
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
- Use `Inertia::optional()` for page-specific expensive data (see Partial Reloads)

---

---

## Partial Reloads

### Optional Props

```php
final class ShowController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => TicketResource::make($ticket),

            // Only loaded on explicit partial reload request
            'comments' => Inertia::optional(
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
    }
}
```

> Note: `Inertia::lazy()` was removed in Inertia Laravel v3. Use `Inertia::optional()` instead — it has identical behavior.

### When to Use
- `Inertia::optional()` — Data that is expensive to compute and not needed on initial page load (e.g., comments, activity logs, related items). The frontend must explicitly request it.
- `Inertia::always()` — Data that must always be fresh, even during partial reloads (e.g., permissions, notification counts).
- Default (no wrapper) — Data that should load on every full page visit.

### Deferred Props

`Inertia::defer()` loads data in a separate request **after** the initial page renders. The page loads immediately with its core data, and the deferred props are fetched automatically in the background. No extra client-side code is needed — Inertia triggers the follow-up request on its own.

```php
final class ShowController extends Controller
{
    public function __invoke(Request $request, Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => TicketResource::make($ticket),

            // Loaded automatically after initial page render
            'activityLog' => Inertia::defer(
                fn () => ActivityResource::collection($ticket->activities()->latest()->get()),
            ),

            // Group related deferred props (second arg) to batch them in one request
            'notifications' => Inertia::defer(
                fn () => NotificationResource::collection($request->user()->unreadNotifications),
                'sidebar',
            ),
            'onlineUsers' => Inertia::defer(
                fn () => UserResource::collection(User::online()->get()),
                'sidebar',
            ),
        ]);
    }
}
```

Use `Inertia::defer()` for data that is needed on the page but not critical for the first paint (e.g., activity logs, notifications, secondary panels). Pass a group name as the second argument to batch related deferred props into a single follow-up request.

> Note: In Inertia Laravel v2 grouping was done via a chained `->group('name')`. v3 removed the chain method — pass the group as the second positional argument to `defer()` instead.

### Prop Types Summary

| Method | Behavior |
|--------|----------|
| Default (no wrapper) | Included on every full page visit |
| `Inertia::optional()` | Excluded on first visit, loaded only on explicit partial reload (replaces v2's `lazy()`) |
| `Inertia::defer()` | Loaded in a separate request after the initial page load |
| `Inertia::always()` | Always included, even during partial reloads |
| `Inertia::merge()` | Merges with existing prop data instead of replacing (useful for infinite scroll) |
| `Inertia::once()` | Loaded once on first visit, then cached client-side and reused across navigations |

```php
// Optional — only loaded on explicit partial reload (frontend opts in)
'stats' => Inertia::optional(
    fn () => StatsResource::make($ticket),
),

// Deferred — loaded automatically in a follow-up request after initial render
'notifications' => Inertia::defer(
    fn () => NotificationResource::collection($user->notifications),
),

// Merge — appends to existing data (infinite scroll / pagination)
'tickets' => Inertia::merge(
    fn () => TicketResource::collection(
        Ticket::query()->paginate(15),
    ),
),

// Once — expensive lookup loaded only once and cached on the client
'timezones' => Inertia::once(fn () => timezone_identifiers_list()),
```

For data that should be shared globally and only fetched once across all Inertia responses, use the `shareOnce()` method in `HandleInertiaRequests` (companion to `share()`):

```php
public function shareOnce(Request $request): array
{
    return [
        'permissions' => fn () => PermissionService::forUser($request->user()),
        'featureFlags' => fn () => FeatureFlag::all(),
    ];
}
```

---
