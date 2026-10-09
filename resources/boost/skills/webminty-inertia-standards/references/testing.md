# Webminty Inertia: Testing

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
