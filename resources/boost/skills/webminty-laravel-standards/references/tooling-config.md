# Webminty Laravel & PHP: Pint, Rector & PHPStan Config

Write code that already passes these tools so no fix-up round is needed. The settings below match the Webminty quickstart's `pint.json`, `rector.php` and `phpstan.neon`.

## Pint (preset `laravel` plus these rules)

| Rule | What it means for the code you write |
|------|--------------------------------------|
| `declare_strict_types` | Every file starts with `declare(strict_types=1)` |
| `ordered_class_elements` | Order: traits, cases, constants, properties (public, protected, private), constructor, magic, then methods public, protected, private; static before non-static. `casts()` (protected) goes after the public relationships |
| `binary_operator_spaces` (`=>` align) | Multi-line array arrows are aligned |
| `global_namespace_import`, `fully_qualified_strict_types` | Always `use` classes, constants and functions; never `\Foo` or relative names such as `Tickets\IndexController` |
| `ordered_imports`, `ordered_traits`, `ordered_interfaces` | Alphabetical |
| `explicit_string_variable` | `"{$name}"`, never `"$name"` |
| `strict_comparison`, `is_null` | `===`/`!==`; `=== null`, not `is_null()` |
| `no_useless_else`, `no_superfluous_elseif`, `simplified_if_return` | No `else` after a return; return conditions directly |
| `mb_str_functions`, `modernize_strpos` | `mb_*` string functions; `str_contains()` and friends |
| `date_time_immutable` | `CarbonImmutable` / `DateTimeImmutable` |
| `concat_space` (one) | `$a . $b` |
| `void_return` | `void` on methods that return nothing |
| `new_with_parentheses: false` | `new User`; keep the parentheses when chaining: `new User()->name` |
| `blank_line_before_statement` | Blank line before `return`, `if`, `foreach` and similar |
| `method_chaining_indentation` | Each chained call on its own line, indented |
| `types_spaces` (single) | Spaces around `|` in native union types: `string | int` |

## PHPStan

- Larastan with `level: 5`, analysing `app/`
- `checkModelProperties: true` and `parseModelCastsMethod: true`: model columns must be backed by the migration and `casts()`, and iterables need generics in a docblock
- `treatPhpDocTypesAsCertain: false`
- Do not add `@phpstan-ignore` comments or baseline entries; fix the type

## Rector

- `withComposerBased(laravel: true)` and `withPhpSets()` (PHP version from `composer.json`)
- Prepared sets: dead code, code quality, type declarations, privatization, early return
- Paths: `app`, `database`, `public`, `tests`

## Composer Scripts

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
