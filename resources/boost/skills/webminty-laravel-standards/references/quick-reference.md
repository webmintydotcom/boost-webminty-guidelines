# Webminty Laravel & PHP: Quick Reference

## Quick Reference

### Must-Have in Every PHP File
- `declare(strict_types=1)`
- `final` class declaration
- Explicit return types on all methods
- Type hints on all parameters

### Class Conventions
- Actions: `final class VerbNoun { public function execute(): ... }`
- Models: `final class Noun extends Model` with `$guarded = ['id']` and `casts()` method
- DTOs: `final class NounData extends Data`
- Jobs: `final class VerbNoun implements ShouldQueue`
- Commands: `final class VerbNoun extends Command` with `app:kebab-case` signature

### Validation
- Always use array syntax: `['required', 'string', 'max:255']`
- Use Form Requests for controllers

### Database
- `$guarded = ['id']` not `$fillable`
- `casts()` method not `$casts` property
- `#[Scope]` attribute not `scopeX()` methods
- `hash_id` pattern for public-facing IDs
- Boolean columns: `is_` or `has_` prefix
- Anonymous migrations: `return new class extends Migration`

### Code Quality
- Use `===`/`!==` (strict comparison)
- Prefer early returns over nested if/else
- Use string interpolation over concatenation
- No `dd()`, `dump()`, `ray()`, `var_dump()` in committed code
- Run `composer quality` (Pint + PHPStan + Pest) before committing

### Laravel 13+ Additions
- Prefer declarative attributes for model config (`#[Table]`, `#[UsePolicy]`), controller middleware (`#[Middleware]`, `#[Authorize]`), and job config (`#[Tries]`, `#[Timeout]`)
- `VerifyCsrfToken` renamed to `PreventRequestForgery`
- Do not instantiate models inside `boot()` or `booted()` — use `booted()` for event callbacks only
- Requires Pest 4 / PHPUnit 12
