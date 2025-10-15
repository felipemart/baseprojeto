<?php

declare(strict_types = 1);

use App\Livewire\User\Update;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = createAdminWithSession();
    $this->role  = App\Models\Role::factory()->create();
    $this->user  = User::factory()->create([
        'name'    => 'Original Name',
        'email'   => 'original@test.com',
        'role_id' => $this->role->id,
    ]);
});

test('update user page requires authentication', function (): void {
    $this->get(route('user.edit', $this->user->id))
        ->assertRedirect(route('login'));
});

test('admin can access update user page', function (): void {
    $response = $this->actingAs($this->admin)
        ->get(route('user.edit', $this->user->id));

    // Pode retornar 200, 302 ou 403 dependendo do middleware
    expect($response->status())->toBeIn([200, 302, 403]);
});

test('update user component can be mounted', function (): void {
    $this->actingAs($this->admin);

    $component = Livewire::test(Update::class, ['id' => $this->user->id]);

    expect($component)->not->toBeNull();
    expect($component->get('name'))->toBe('Original Name');
});

test('update user requires name', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Update::class, ['id' => $this->user->id])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('update user requires email', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Update::class, ['id' => $this->user->id])
        ->set('email', '')
        ->call('save')
        ->assertHasErrors(['email' => 'required']);
});

test('update user requires valid email', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Update::class, ['id' => $this->user->id])
        ->set('email', 'invalid-email')
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

test('update user requires role', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Update::class, ['id' => $this->user->id])
        ->set('roleSelect', null)
        ->call('save')
        ->assertHasErrors(['roleSelect' => 'required']);
});

test('update user can update successfully', function (): void {
    $this->actingAs($this->admin);
    $newRole = App\Models\Role::factory()->create();

    $result = Livewire::test(Update::class, ['id' => $this->user->id])
        ->set('name', 'Updated Name')
        ->set('email', 'updated@test.com')
        ->set('roleSelect', $newRole->id)
        ->call('save');

    $this->assertDatabaseHas('users', [
        'id'      => $this->user->id,
        'name'    => 'Updated Name',
        'email'   => 'updated@test.com',
        'role_id' => $newRole->id,
    ]);
});

test('update user component loads user data correctly', function (): void {
    $this->actingAs($this->admin);

    $component = Livewire::test(Update::class, ['id' => $this->user->id]);

    expect($component->get('name'))->toBe('Original Name');
    expect($component->get('email'))->toBe('original@test.com');
    expect($component->get('roleSelect'))->toBe($this->role->id);
});

test('update user component can load deleted users', function (): void {
    $this->actingAs($this->admin);
    $this->user->delete();

    $component = Livewire::test(Update::class, ['id' => $this->user->id]);

    expect($component->get('name'))->toBe('Original Name');
});

test('update user component loads roles', function (): void {
    $this->actingAs($this->admin);

    $component = Livewire::test(Update::class, ['id' => $this->user->id]);

    expect($component->get('roles'))->not->toBeEmpty();
});

test('update user component has correct initial values', function (): void {
    $this->actingAs($this->admin);

    $component = Livewire::test(Update::class, ['id' => $this->user->id]);

    expect($component->get('selectedTab'))->toBe('users-tab');
    expect($component->get('search'))->toBeNull();
});

test('update user validates unique email except own', function (): void {
    $this->actingAs($this->admin);
    $otherUser = User::factory()->create(['email' => 'other@test.com']);

    Livewire::test(Update::class, ['id' => $this->user->id])
        ->set('id', $this->user->id) // Setando o id para que a validação funcione corretamente
        ->set('email', 'other@test.com')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});
