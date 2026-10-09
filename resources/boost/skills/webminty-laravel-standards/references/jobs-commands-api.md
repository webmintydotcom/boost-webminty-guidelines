# Webminty Laravel & PHP: Jobs, Commands, API & Batches

## Jobs

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessOrder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    public function __construct(
        public Order $order,
    ) {}

    public function handle(PaymentService $payment): void
    {
        $payment->charge($this->order);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Order processing failed', [
            'order_id' => $this->order->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
```

### Job Attributes (Laravel 13+)

Laravel 13 introduces declarative attributes for job configuration:

```php
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\WithoutRelations;

#[Tries(3)]
#[Backoff(60)]
#[Timeout(120)]
#[Connection('redis')]
#[Queue('orders')]
final class ProcessOrder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        #[WithoutRelations]
        public Order $order,
    ) {}

    public function handle(PaymentService $payment): void
    {
        $payment->charge($this->order);
    }
}
```

On Laravel 12, continue using property-based configuration (`public int $tries = 3`, etc.).

### Key Rules
- Jobs must be `final` and implement `ShouldQueue`
- Use one trait per line
- Inject dependencies in `handle()`, not constructor
- Store minimal data (models serialize to IDs)
- Keep jobs small and focused
- Always handle failures with `failed()` method
- Use middleware for rate limiting and preventing overlaps
- On Laravel 13+, prefer job attributes (`#[Tries]`, `#[Timeout]`, etc.) over property configuration

### Dispatching

```php
ProcessOrder::dispatch($order);
ProcessOrder::dispatch($order)->delay(now()->addMinutes(5));
ProcessOrder::dispatch($order)->onQueue('orders');
```

### Job Chains

```php
Bus::chain([
    new ProcessPayment($order),
    new UpdateInventory($order),
    new SendConfirmation($order),
])->dispatch();
```

---

---

## Commands

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class CleanupInactiveUsers extends Command
{
    protected $signature = 'app:cleanup-inactive-users
                            {--days=30 : Days of inactivity threshold}
                            {--dry-run : Run without making changes}';

    protected $description = 'Deactivate users who have been inactive';

    public function handle(DeactivateInactiveUsers $deactivateUsers): int
    {
        $count = $deactivateUsers->execute(
            days: (int) $this->option('days'),
            dryRun: (bool) $this->option('dry-run'),
        );

        $this->info("{$count} users processed.");

        return Command::SUCCESS;
    }
}
```

### Key Rules
- Use kebab-case for command signatures prefixed with `app:`
- Inject dependencies in `handle()`, not constructor
- Delegate business logic to Actions
- Always provide descriptions
- Support dry-run for destructive commands
- Return `Command::SUCCESS` or `Command::FAILURE`

### Scheduling

```php
Schedule::command('app:cleanup-inactive-users')
    ->daily()
    ->at('03:00')
    ->withoutOverlapping()
    ->onOneServer();
```

---

---

## API Standards

### Versioning
Include version in URL:

```php
Route::prefix('api/v1')->group(function () { ... });
```

- Always version APIs from the start (`v1`), even for internal APIs.
- Use URL-based versioning (`/api/v1/...`) — not header-based versioning.
- When introducing breaking changes, create a new version (`v2`) while keeping the previous version supported during a deprecation period.
- Keep controller namespaces organized by version: `App\Http\Controllers\Api\V1\`, `App\Http\Controllers\Api\V2\`.
- Route files can use separate groups or files per version for clarity.

```
app/Http/Controllers/Api/
├── V1/
│   ├── TicketController.php
│   └── UserController.php
└── V2/
    ├── TicketController.php
    └── UserController.php
```

### Naming
- Plural nouns for resource names: `/api/v1/tickets`
- No verbs in URLs

### HTTP Methods

| Method | Purpose |
|--------|---------|
| GET | Retrieve resource(s) |
| POST | Create a resource |
| PUT | Replace entire resource |
| PATCH | Partial update |
| DELETE | Remove a resource |

### Response Format
Use Laravel API Resources:

```php
final class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hash_id,
            'title' => $this->title,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

### Authentication
Use Laravel Sanctum for API authentication.

### Rate Limiting
Apply rate limits liberally:
- Always on auth endpoints and email-sending endpoints
- Always on POST requests and expensive operations
- Recommended on all public/unauthenticated endpoints

### Key Rules
- Always return JSON
- Use `hash_id` in URLs, not internal IDs
- Never expose sensitive data
- Use Form Requests for validation
- Use proper HTTP status codes (201 for created, 204 for no content, 422 for validation)

---

---

## Batches vs Pipelines

### When to Use Each

| Scenario | Pipeline | Batch |
|----------|----------|-------|
| Simple data transformation | Yes | No |
| Single value passed between steps | Yes | No |
| Multiple parameters needed | No | Yes |
| Conditional logic between steps | No | Yes |
| Different error handling per step | No | Yes |
| Linear A -> B -> C flow | Yes | No |

### Pipeline Example

```php
app(Pipeline::class)
    ->send($feet)
    ->through([
        ConvertFeetToInches::class,
        ConvertInchesToCentimeters::class,
        ConvertCentimetersToMeters::class,
    ])
    ->thenReturn();
```

### Batch Example

```php
final class CreateUserBatch
{
    public function handle(UserData $data, array $slackChannels): User
    {
        $user = $this->createUser->execute($data);
        $this->setupGitHub($user);
        $this->setupSlack($user, $slackChannels);
        $this->sendWelcomeEmail->execute($user);
        return $user;
    }
}
```

### File Organization

```
app/
├── Batches/           # Batch orchestrators
├── Pipes/             # Pipeline step classes
└── Actions/           # Reusable single actions
```

---
