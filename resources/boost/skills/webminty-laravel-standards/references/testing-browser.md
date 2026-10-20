# Webminty Laravel & PHP: Pest Browser Tests

Browser tests use `pestphp/pest-plugin-browser` (Playwright). They are slower than Feature tests, so write one only for behaviour a Feature test cannot prove: JavaScript, real navigation, layout, and flows that span pages.

## When to Write One
- A Feature test and an Inertia assertion cannot show it: client-side behaviour, focus, keyboard use, dialogs, drag and drop, file inputs
- A critical journey across pages: register, log in, complete the main task, log out
- Layout or responsive behaviour on a named device
- Do not duplicate what a Feature test already proves; do not test framework or library behaviour

## Setup
- Files live in `tests/Browser`, named for the journey: `LoginTest.php`, `VehicleEditTest.php`
- `tests/Pest.php` binds `Browser` to the app `TestCase` and `RefreshDatabase`, the same as `Feature`
- Playwright browsers must be installed (`npx playwright install chromium`) and the front end built (`npm run build`), or assets will be missing
- Use `test()` syntax with a sentence name, and arrange, act, assert, like every other test

## Writing Tests

```php
<?php

declare(strict_types=1);

use App\Models\User;

test('a signed-in user can edit a vehicle', function (): void {
    $this->actingAs(User::factory()->create());

    visit(route('vehicles.edit', 'FL-1000'))
        ->assertNoJavaScriptErrors()
        ->fill('odometer', '50000')
        ->press('Save changes')
        ->assertPathIs('/vehicles/FL-1000')
        ->assertSee('50,000 mi');
});
```

- Sign in with `$this->actingAs()` before `visit()` unless the test is about signing in; for login tests, fill the real form
- Create data with factories inside the test; never rely on a seeded database
- The plugin resolves a selector in this order: an explicit CSS selector (`#odometer`), `@name` for `data-testid`/`data-test`, a field's `id` then `name` (`'odometer'`), then exact visible text (`'Save changes'`). Fields are found by `id`/`name`, not by label text
- Use the `id` or `name` for fields and the visible text for buttons and links; add `data-testid` and use `@name` when neither is stable. Avoid CSS classes and structural selectors
- Prefer one `assertNoJavaScriptErrors()` after the page loads so script failures fail the test
- Wait for results rather than sleeping: use `waitForText()` or an assertion that retries, never `wait()` with a guess
- Assert outcomes (URL, visible text, database state), not implementation details
- Use `->on()->mobile()` or a named device when layout matters, and `assertNoAccessibilityIssues()` on key pages
- Keep each test to one journey; share setup with `beforeEach()`

## Useful API

| Need | Use |
|------|-----|
| Open a page | `visit(route('name'))`, `visit([...urls])` for several |
| Fill and submit | `fill()`, `type()`, `select()`, `check()`, `press()`, `click()` |
| Assert text and state | `assertSee()`, `assertDontSee()`, `assertSeeIn()`, `assertVisible()`, `assertMissing()`, `assertCount()` |
| Assert location | `assertPathIs()`, `assertRoute()`, `assertQueryStringHas()` |
| Health checks | `assertNoJavaScriptErrors()`, `assertNoConsoleLogs()`, `assertNoBrokenImages()`, `assertNoSmoke()` |
| Wait | `waitForText()`, `waitForEvent()` |
| Device and mode | `->on()->mobile()`, `->inDarkMode()`, `->withLocale()` |
| Debug | `->screenshot()`, `->debug()`, `->headed()` (config) |

## Running
- Run browser tests on their own: `vendor/bin/pest tests/Browser`
- Run them separately from the Feature suite; they need a built front end and installed browsers
- When a test fails, read the failure and the screenshot before changing anything; decide whether the test or the code is wrong
- Never mark a flaky test skipped to make a run pass; fix the wait or the selector
