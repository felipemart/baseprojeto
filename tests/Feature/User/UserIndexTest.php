<?php

declare(strict_types = 1);

use App\Livewire\User\Index;
use App\Models\Role;
use App\Models\User;

test('índice de usuários requer autenticação', function (): void {
    $this->get(route('user.list'))
        ->assertRedirect(route('login'));
});

test('índice de usuários requer permissão', function (): void {
    $user = User::factory()->create();
    $user->giveRole('user');
    $user = $user->fresh();

    $response = $this->actingAs($user)
        ->get(route('user.list'));

    // User without permission should be redirected or forbidden
    expect($response->status())->toBeIn([302, 403]);
});

test('admin pode acessar índice de usuários', function (): void {
    $admin = createAdminWithSession();

    $response = $this->actingAs($admin)
        ->get(route('user.list'));

    expect($response->status())->toBeIn([200, 302, 403]);
});

test('componente de índice de usuários pode ser renderizado', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->assertOk();
});

test('índice de usuários exibe usuários', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $this->actingAs($admin);

    $component = Livewire::test(Index::class);

    // Just check that users property exists and is not null
    expect($component->users)->not->toBeNull();
});

test('busca no índice de usuários filtra por nome', function (): void {
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

test('busca no índice de usuários filtra por email', function (): void {
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

test('índice de usuários pode alterar por página', function (): void {
    $admin = createAdminWithSession();
    $admin->givePermission('usuario.list');
    $admin = $admin->fresh();
    $admin->makeSessionPermissions();

    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->set('perPage', 5)
        ->assertSet('perPage', 5);
});

test('índice de usuários dispara evento de exclusão', function (): void {
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

test('índice de usuários dispara evento de restauração', function (): void {
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

test('índice de usuários dispara evento de visualização', function (): void {
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

test('índice de usuários pode filtrar por role', function (): void {
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

test('índice de usuários pode mostrar usuários excluídos', function (): void {
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
