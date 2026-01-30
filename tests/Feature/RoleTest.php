<?php

declare(strict_types = 1);

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

test('usuário pode receber uma role', function (): void {
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

test('usuário pode verificar se tem uma role', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');

    // Recarregar primeiro, depois autenticar com objeto atualizado
    $user = $user->fresh();
    $this->actingAs($user);
    $user->makeSessionRoles();

    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('user'))->toBeFalse();
});

test('usuário pode verificar múltiplas roles', function (): void {
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

test('dar role cria ela se não existir', function (): void {
    $user = User::factory()->create();

    $user->giveRole('newrole');

    assertDatabaseHas('roles', [
        'name' => 'Newrole',
    ]);
});

test('usuário pode ter apenas uma role por vez', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');
    $firstRoleId = $user->fresh()->role_id;

    $user->giveRole('user');
    $secondRoleId = $user->fresh()->role_id;

    expect($firstRoleId)->not->toBe($secondRoleId);
    expect($user->fresh()->hasRole('admin'))->toBeFalse();
    expect($user->fresh()->hasRole('user'))->toBeTrue();
});

test('sessão de role é criada quando role é atribuída', function (): void {
    $user = User::factory()->create();

    $user->giveRole('admin');

    // Recarregar primeiro, depois autenticar
    $user = $user->fresh();
    $this->actingAs($user);
    $user->makeSessionRoles();

    expect(session()->has("user:{$user->id}.roles"))->toBeTrue();
});

test('relacionamento de role funciona corretamente', function (): void {
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
