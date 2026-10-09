# Webminty Laravel & PHP: Models, Eloquent & Migrations

## Models

### Basic Structure

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasHashIds;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Ticket extends Model
{
    use HasFactory;
    use HasHashIds;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'due_at' => 'datetime',
            'settings' => 'array',
            'status' => Status::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketItem::class)
            ->orderBy('position');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
```

### Model Attributes (Laravel 13+)

Laravel 13 introduces declarative attributes for model configuration. Use these instead of properties when available:

```php
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;

#[Table('custom_tickets')]
#[Connection('mysql')]
#[UsePolicy(TicketPolicy::class)]
final class Ticket extends Model
{
    // ...
}
```

| Attribute | Purpose | Replaces |
|-----------|---------|----------|
| `#[Table('name')]` | Set table name | `protected $table` |
| `#[Connection('name')]` | Set database connection | `protected $connection` |
| `#[UsePolicy(Policy::class)]` | Associate policy | Manual policy mapping |
| `#[WithoutTimestamps]` | Disable timestamps | `public $timestamps = false` |
| `#[WithoutIncrementing]` | Disable auto-increment | `public $incrementing = false` |
| `#[DateFormat('U')]` | Custom date format | `protected $dateFormat` |

On Laravel 12, continue using the property-based approach.

### Key Rules
- Use `$guarded = ['id']` instead of `$fillable`
- Use the `casts()` method (not `$casts` property)
- Use `#[Scope]` attribute for query scopes (Laravel 11+)
- Always type relationship return values (`BelongsTo`, `HasMany`, etc.)
- Use one trait per line
- Singular names for belongsTo/hasOne, plural for hasMany/belongsToMany
- On Laravel 13+, prefer model attributes (`#[Table]`, `#[UsePolicy]`, etc.) over property configuration
- Do not instantiate models inside `boot()` or `booted()` — Laravel 13 throws a `LogicException` if you do

### Common Cast Types
- `boolean` for `is_*` and `has_*` columns
- `datetime` for timestamp columns
- `array` for JSON columns
- `decimal:2` for money/precise decimals
- `Enum::class` for enum columns
- `Data::class` for Spatie LaravelData columns

### HasHashIds Trait
Generate URL-safe public IDs. Uses the `booted()` method to register the observer — this avoids the Laravel 13 restriction against instantiating models inside `boot()`:

```php
trait HasHashIds
{
    protected static function booted(): void
    {
        static::created(function ($model): void {
            $reflect = new ReflectionClass(self::class);
            $connection = Str::lower($reflect->getShortName());

            if ($model->hash_id) {
                return;
            }

            $model->hash_id = Hashids::connection($connection)
                ->encode((string) $model->id);
            $model->save();
        });
    }
}
```

### Accessors and Mutators
Use `Attribute::make()` syntax:

```php
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn () => "{$this->first_name} {$this->last_name}",
    );
}
```

### Model Safety (AppServiceProvider)

```php
public function boot(): void
{
    Model::preventLazyLoading();

    if ($this->app->isProduction()) {
        Model::handleLazyLoadingViolationUsing(
            function ($model, $relation): void {
                info("Attempted to lazy load [{$relation}] on model [{$model}].");
            }
        );
        DB::prohibitDestructiveCommands();
    } else {
        Model::shouldBeStrict();
    }
}
```

### Model boot() Restriction (Laravel 13+)

Laravel 13 throws a `LogicException` if you instantiate a model inside `boot()` or `booted()`. Move any model-creating logic to observers or event listeners. Use `booted()` (not `boot()`) for registering model event callbacks — this is compatible with both Laravel 12 and 13.

---

---

## Eloquent

### Query Builder
Always start queries with `query()` for clarity:

```php
$tickets = Ticket::query()
    ->with(['user', 'category'])
    ->where('status', Status::Open)
    ->latest()
    ->paginate(15);
```

### Query Scopes

```php
// Using #[Scope] attribute (preferred)
#[Scope]
protected function active(Builder $query): void
{
    $query->where('is_active', true);
}

#[Scope]
protected function forUser(Builder $query, User $user): void
{
    $query->where('user_id', $user->id);
}

// Usage
Ticket::active()->forUser($user)->get();
```

### Custom Query Builders
For models with 3+ scopes:

```php
final class UserBuilder extends Builder
{
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    public function search(string $term): self
    {
        return $this->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }
}
```

### Eager Loading
Always eager load to prevent N+1:

```php
$tickets = Ticket::with(['user', 'category', 'items'])->get();

// Constrained
$tickets = Ticket::with([
    'comments' => fn ($query) => $query->latest()->limit(5),
])->get();
```

### Transactions

```php
DB::transaction(function () use ($data): Order {
    $order = Order::create($data);
    $order->items()->createMany($data['items']);
    return $order;
});
```

### Large Datasets
- Use `chunk()` or `chunkById()` for processing
- Use `lazy()` for memory-efficient iteration
- Use `cursor()` for read-only operations

---

---

## Database & Migrations

### Migration Structure

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('hash_id')->nullable()->default(null)->unique();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable()->default(null);
            $table->timestampTz('due_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
```

### Column Conventions

| Type | Convention | Examples |
|------|------------|----------|
| Primary Key | `id` | `id` |
| Hash ID | `hash_id` | `hash_id` |
| Foreign Key | `{model}_id` | `user_id`, `category_id` |
| Boolean | `is_` or `has_` prefix | `is_active`, `has_subscription` |
| Timestamps | `_at` suffix | `due_at`, `published_at` |
| JSON | nullable with default null | `settings`, `metadata` |

### Foreign Keys

```php
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->foreignId('category_id')->nullable()->constrained();
$table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
```

### Indexes

```php
$table->index('is_active');
$table->index(['user_id', 'is_active']);
$table->unique('email');
$table->unique(['name', 'user_id']);
```

### Hash ID Pattern

```php
$table->string('hash_id')->nullable()->default(null)->unique();
```

---
