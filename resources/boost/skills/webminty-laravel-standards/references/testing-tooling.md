# Webminty Laravel & PHP: General Laravel, Linting & Testing

## General Laravel

### CSRF Protection (Laravel 13+)

Laravel 13 renames `VerifyCsrfToken` to `PreventRequestForgery` and adds dual-layer protection (origin verification via `Sec-Fetch-Site` header + traditional CSRF token fallback). If you reference this middleware directly, use the name appropriate for your Laravel version. The old name remains as a deprecated alias in Laravel 13 but should be updated.

### Dependency Injection
- Prefer constructor/method injection
- Use `app()` only when DI isn't possible
- Never inject the container itself

### Helpers vs Facades
- Use helper functions for simple get/set: `session()`, `config()`, `cache()`, `auth()`
- Use facades for chained methods: `Cache::tags()->put()`, `Log::channel()`

### Strings and Arrays
- Use `Str::` helpers over PHP string functions
- Use `Arr::` helpers for array manipulation
- Use collections over array functions

### Configuration
- Use `config()` everywhere, never `env()` outside config files
- Type-cast env values in config files

### Logging
- Use appropriate levels (debug, info, warning, error, critical)
- Always include structured context arrays
- Use channels for specific destinations

### Events

```php
final class OrderPlaced
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Order $order,
    ) {}
}
```

### Custom Exceptions

```php
final class InsufficientFundsException extends Exception
{
    public function __construct(
        public readonly float $available,
        public readonly float $required,
    ) {
        parent::__construct("Insufficient funds: {$available} available, {$required} required");
    }
}
```

---

---

## Linting & Code Quality

### Tools

| Tool | Purpose |
|------|---------|
| Laravel Pint | Code formatting (PHP CS Fixer) |
| PHPStan + Larastan | Static analysis (level 5) |
| Rector | Automated refactoring |

### Key Pint Rules
- `declare_strict_types`: Adds `declare(strict_types=1)`
- `final_class`: Makes classes `final`
- `strict_comparison`: Uses `===` instead of `==`
- `void_return`: Adds `void` return types
- `new_with_parentheses: false`: `new User` instead of `new User()`
- `trailing_comma_in_multiline`: Trailing commas
- `ordered_imports`: Alphabetical imports

### Composer Scripts

```json
{
    "scripts": {
        "lint": "./vendor/bin/pint",
        "lint:check": "./vendor/bin/pint --test",
        "analyse": "./vendor/bin/phpstan analyse",
        "test": "./vendor/bin/pest",
        "quality": ["@lint:check", "@analyse", "@test"]
    }
}
```

---

---

## Testing

### Framework
All projects use **Pest PHP**. Laravel 12 uses Pest 3 / PHPUnit 11. Laravel 13 requires Pest 4 / PHPUnit 12.

### Run Tests

- Always run tests with the `--parallel` flag: `php artisan test --compact --parallel`
- When filtering tests, also use parallel: `php artisan test --compact --parallel --filter=testName`

### Pest Configuration

```php
pest()->extends(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
```

### Test Syntax
Always use `test()` function (not `it()` or class-based):

```php
test('can create a ticket', function (): void {
    $user = User::factory()->create();
    $data = new TicketData(
        title: 'Test Ticket',
        body: 'This is a test ticket.',
        user_id: $user->id,
    );

    $ticket = app(CreateTicket::class)->execute($data);

    expect($ticket)
        ->toBeInstanceOf(Ticket::class)
        ->title->toBe('Test Ticket');
});
```

### Architecture Tests

```php
arch()
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump', 'ray', 'var_dump', 'print_r']);

arch()
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

// Models with casted columns must use the casts() method (not the $casts property).
// Exclude pivots or simple lookup models that have nothing to cast.
arch()
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->ignoring([
        // 'App\Models\Pivots\TeamUser',
    ]);

arch()
    ->expect('App\Actions')
    ->toHaveMethod('execute')
    ->toBeFinal();

arch()
    ->expect('App\Data')
    ->toExtend('Spatie\LaravelData\Data')
    ->toBeFinal();

arch()
    ->expect('App\Http\Controllers')
    ->toBeFinal();

arch()
    ->expect('App\Enums')
    ->toBeEnums();

arch()
    ->expect('App\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue')
    ->toBeFinal();

arch()
    ->expect('App\Http\Requests')
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->toBeFinal();

arch()->preset()->php();
arch()->preset()->security()->ignoring('md5');
arch()->preset()->laravel();
```

### Testing Actions

```php
test('can create a ticket with all fields', function (): void {
    $user = User::factory()->create();
    $data = new TicketData(
        title: 'Test Ticket',
        body: 'Test body.',
        user_id: $user->id,
    );

    $ticket = app(CreateTicket::class)->execute($data);

    expect($ticket)
        ->toBeInstanceOf(Ticket::class)
        ->title->toBe('Test Ticket');
});
```

### Testing HTTP

```php
test('can view dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});
```

### Factory Best Practices

```php
final class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'is_active' => true,
            'user_id' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
```

---
