<?php

declare(strict_types = 1);

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

test('user can be assigned a role', function (): void {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'Admin']);

    $user->giveRole('admin');

    // Recarregar primeiro, depois autenticar com objeto atualizado
    $user = $user->fresh();
    $this->actingAs($user);
    $user->makeSessionRoles();

    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->role_id)->not->toBeNull();
});

test('user can check if has a role', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');

    // Recarregar primeiro, depois autenticar com objeto atualizado
    $user = $user->fresh();
    $this->actingAs($user);
    $user->makeSessionRoles();

    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('user'))->toBeFalse();
});

test('user can check multiple roles', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');

    // Recarregar primeiro, depois autenticar com objeto atualizado
    $user = $user->fresh();
    $this->actingAs($user);
    $user->makeSessionRoles();

    // Verifica que tem a role admin
    expect($user->hasRole('admin'))->toBeTrue();

    // Verifica que não tem outras roles
    expect($user->hasRole('user'))->toBeFalse();
    expect($user->hasRole('guest'))->toBeFalse();
});

test('giving role creates it if not exists', function (): void {
    $user = User::factory()->create();

    $user->giveRole('newrole');

    assertDatabaseHas('roles', [
        'name' => 'Newrole',
    ]);
});

test('user can only have one role at a time', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');
    $firstRoleId = $user->fresh()->role_id;

    $user->giveRole('user');
    $secondRoleId = $user->fresh()->role_id;

    expect($firstRoleId)->not->toBe($secondRoleId);
    expect($user->fresh()->hasRole('admin'))->toBeFalse();
    expect($user->fresh()->hasRole('user'))->toBeTrue();
});

test('role session is created when role is assigned', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');

    // Recarregar primeiro, depois autenticar
    $user = $user->fresh();
    $this->actingAs($user);
    $user->makeSessionRoles();

    expect(session()->has("user:{$user->id}.roles"))->toBeTrue();
});

test('role relationship works correctly', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');

    // Recarregar para obter dados atualizados
    $user = $user->fresh();

    // Verifica que o relacionamento existe e tem role_id
    expect($user->role_id)->not->toBeNull();

    // Verifica que existe uma role associada no banco
    $role = Role::find($user->role_id);
    expect($role)->not->toBeNull();
    expect($role->name)->toBe('Admin');
});
