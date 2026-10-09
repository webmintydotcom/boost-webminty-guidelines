# Webminty Livewire 4: Testing & Architecture Tests

## Testing

### Component Rendering

```php
test('can render ticket list', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TicketList::class)
        ->assertOk();
});
```

### Form Submission

```php
test('can create a ticket', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateTicketPage::class)
        ->set('form.title', 'Test Ticket')
        ->set('form.body', 'This is a test ticket body.')
        ->call('save')
        ->assertRedirect(route('tickets.index'));

    expect(Ticket::where('title', 'Test Ticket')->exists())->toBeTrue();
});
```

### Validation

```php
test('title is required', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateTicketPage::class)
        ->set('form.title', '')
        ->call('save')
        ->assertHasErrors(['form.title' => 'required']);
});
```

### Events

```php
test('dispatches event after creation', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateTicketPage::class)
        ->set('form.title', 'Test')
        ->set('form.body', 'Test body content.')
        ->call('save')
        ->assertDispatched('ticket-created');
});
```

---

---

## Architecture Tests

```php
// For class-based components
arch()
    ->expect('App\Livewire')
    ->toExtend('Livewire\Component')
    ->toBeFinal();
```
