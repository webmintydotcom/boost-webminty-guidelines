# Webminty Laravel & PHP: Actions, Controllers, Routes & Blade

## Actions

### Overview
Actions are single-purpose classes that encapsulate business logic with a single `execute()` method.

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Data\TicketData;
use App\Models\Ticket;

class CreateTicket
{
    public function execute(TicketData $data): Ticket
    {
        return Ticket::query()->create([
            'title'       => $data->title,
            'body'        => $data->body,
            'user_id'     => $data->user_id,
            'category_id' => $data->category_id,
        ]);
    }
}
```

### Key Rules
- Single responsibility: one action per class
- Use DTOs for complex parameters
- Map DTO fields to the model explicitly; never pass `$request->all()` or a whole DTO into `create()`/`update()`. Every column written must be listed in the model's `#[Fillable]` (non-fillable keys are silently dropped)
- Return the created model, `void` for updates/deletes, or the computed result
- Verb-first naming: `CreateTicket`, `UpdateItem`, `ConvertKilogramsToPounds`
- Use constructor injection for dependencies
- Organize in subdirectories by domain: `app/Actions/Tickets/`

### Calling Actions

```php
// Via container
$ticket = app(CreateTicket::class)->execute($data);

// Via dependency injection
public function store(CreateTicket $createTicket, TicketData $data): Response
{
    $ticket = $createTicket->execute($data);
    return redirect()->route('tickets.show', $ticket);
}
```

### Complex Actions

```php
class ProcessOrder
{
    public function __construct(
        private readonly ValidateInventory $validateInventory,
        private readonly CreatePayment $createPayment,
        private readonly SendConfirmation $sendConfirmation,
    ) {}

    public function execute(OrderData $data): Order
    {
        $this->validateInventory->execute($data->items);
        $order = Order::query()->create([
            'user_id' => $data->user_id,
            'total' => $data->total,
        ]);
        $this->createPayment->execute($order);
        $this->sendConfirmation->execute($order);
        return $order;
    }
}
```

### When to Create an Action
- Logic is used in multiple places
- Logic requires more than a few lines
- Logic has business rules or validation
- Logic has side effects (events, notifications, cache)
- Logic needs independent testing

---

---

## Controllers

### Responsibilities
Controllers should be thin:
- Handle HTTP requests and return HTTP responses
- Validate input via Form Requests
- Authorize actions via policies or gates
- Delegate business logic to Actions

### Single Action Controllers (Preferred)

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tickets;

use App\Actions\Tickets\CreateTicket;
use App\Data\TicketData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\StoreTicketRequest;
use Illuminate\Http\RedirectResponse;

class StoreController extends Controller
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

### Controller Attributes

Laravel 13 introduces declarative attributes for middleware and authorization on controllers:

```php
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth', 'verified')]
class ShowController extends Controller
{
    #[Authorize('view', [Ticket::class, 'ticket'])]
    public function __invoke(Ticket $ticket): Response
    {
        return Inertia::render('Tickets/Show', [
            'ticket' => TicketsResource::make($ticket),
        ]);
    }
}
```

| Attribute | Purpose | Replaces |
|-----------|---------|----------|
| `#[Middleware('auth')]` | Apply middleware to controller or method | `$this->middleware()` / route middleware |
| `#[Authorize('ability', [Model::class])]` | Policy authorization on method | `$this->authorize()` |

Pick one authorization style per controller: use `#[Authorize]` for simple policy checks on a route-bound model, and fall back to `$this->authorize()` / `Gate::` inside the method only when the check depends on runtime logic (for example, an ability that needs a value computed in the method). Do not stack both for the same ability.


### Policy Naming
- Policy methods use Laravel's standard names: `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`
- Use `view`, not `show`
- Custom abilities and `Gate::define()` names are camelCase: `Gate::define('publishPost', ...)`

### Parameter Order
1. Route model bindings
2. Form Request
3. Injected dependencies

### Form Requests
Always use Form Requests for validation with array syntax:

```php
class StoreTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ];
    }
}
```

Prefer custom rule classes (`php artisan make:rule`) over `Validator::extend()`. Any string-registered rule uses a snake_case name, e.g. `organisation_type`.

### File Organization

```
app/Http/Controllers/
├── Controller.php
├── DashboardController.php
├── Tickets/
│   ├── IndexController.php
│   ├── StoreController.php
│   └── ShowController.php
└── Api/
    └── Tickets/
        └── IndexController.php
```

---

---

## Routes

### Basic Structure

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/tickets', TicketIndexController::class)->name('tickets.index');
});

require __DIR__ . '/auth.php';
```

### Naming
- Route names: dot notation (`tickets.show`, `tickets.index`)
- URLs: kebab-case (`/about-us`, `/user-profile`)
- Always name routes for maintainability

### RESTful Naming

| Verb | URI | Route Name |
|------|-----|------------|
| GET | /tickets | tickets.index |
| GET | /tickets/create | tickets.create |
| POST | /tickets | tickets.store |
| GET | /tickets/{ticket} | tickets.show |
| PUT/PATCH | /tickets/{ticket} | tickets.update |
| DELETE | /tickets/{ticket} | tickets.destroy |

### Route Model Binding

```php
Route::get('/tickets/{ticket:hash_id}', TicketShowController::class);
```

### Key Rules
- Use single-action controllers or resource controllers for route targets
- Group routes by middleware
- Separate auth routes into `routes/auth.php`
- Avoid logic in route files

---

---

## Blade Templates

### Naming
- Use kebab-case for file names: `ticket-list.blade.php`
- Use kebab-case for component names: `<x-ui.button-group>`

### Components
Prefer `x-` component syntax over `@include`:

```php
<x-ui.card>
    <x-slot:header>Card Title</x-slot:header>
    Card content here
</x-ui.card>
```

### Data Handling
- All data should come from controllers
- No Eloquent queries in views
- Simple formatting is acceptable: `{{ $date->format('M j, Y') }}`

### Translations
Always use the `__()` translation helper; never use `@lang`:

```php
<h1>{{ __('tickets.index.title') }}</h1>
```

### CSS
Use Tailwind v4 (CSS-first config via `@import "tailwindcss"` and `@theme`; no `tailwind.config.js`).

See the `webminty-tailwind-standards` skill for the full Tailwind guidelines (setup, theme tokens, utility usage, class ordering, variants, dark mode, component extraction, and v3-to-v4 migration).

### JavaScript
Use Alpine.js for simple interactivity. Prefer external files for larger scripts.

### File Organization

```
resources/views/
├── components/
│   ├── layouts/
│   ├── ui/
│   └── forms/
├── pages/
├── partials/
└── emails/
```

---
