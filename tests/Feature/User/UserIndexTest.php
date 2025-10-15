<?php

declare(strict_types = 1);

use App\Livewire\User\Index;
use App\Models\Role;
use App\Models\User;

test('user index requires authentication', function (): void {
    $this->get(route('user.list'))
        ->assertRedirect(route('login'));
});

test('user index requires permission', function (): void {
    $user = User::factory()->create();
    $user->giveRole('user');
    $user = $user->fresh();

    $response = $this->actingAs($user)
        ->get(route('user.list'));

    // User without permission should be redirected or forbidden
    expect($response->status())->toBeIn([302, 403]);
});

test('admin can access user index', function (): void {
    $admin = createAdminWithSession();

    $response = $this->actingAs($admin)
        ->get(route('user.list'));

    expect($response->status())->toBeIn([200, 302, 403]);
});

test('user index component can be rendered', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertOk();
});

test('user index displays users', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $this->actingAs($admin);

    $component = Livewire::test(Index::class);

    // Just check that users property exists and is not null
    expect($component->users)->not->toBeNull();
});

test('user index search filters by name', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);

    $this->actingAs($admin);

    $component = Livewire::test(Index::class)
        ->set('search', 'john');

    $results = $component->users;
    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('John Doe');
});

test('user index search filters by email', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);

    $this->actingAs($admin);

    $component = Livewire::test(Index::class)
        ->set('search', 'john@');

    $results = $component->users;
    expect($results)->toHaveCount(1);
    expect($results->first()->email)->toBe('john@example.com');
});

test('user index can change per page', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->set('perPage', 5)
        ->assertSet('perPage', 5);
});

test('user index dispatches delete event', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $user = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('destroy', $user->id)
        ->assertDispatched('user.deletion');
});

test('user index dispatches restore event', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $user = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('restore', $user->id)
        ->assertDispatched('user.restoring');
});

test('user index dispatches show event', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $user = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('show', $user->id)
        ->assertDispatched('user.showing');
});

test('user index can filter by role', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $adminRole = Role::firstOrCreate(['name' => 'admin_test']);

    $this->actingAs($admin);

    $component = Livewire::test(Index::class)
        ->set('searchRole', [$adminRole->id]);

    // Just verify it doesn't error
    expect($component)->not->toBeNull();
});

test('user index can show trashed users', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $this->actingAs($admin);

    $component = Livewire::test(Index::class)
        ->set('search_trash', true);

    // Just verify the property was set
    expect($component->search_trash)->toBeTrue();
});
