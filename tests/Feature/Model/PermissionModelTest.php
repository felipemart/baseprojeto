<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

test('permissão pode ser criada', function (): void {
    $role       = Role::factory()->create();
    $permission = Permission::factory()->create([
        'permission' => 'create-post',
        'descricao'  => 'Can create posts',
        'role_id'    => $role->id,
    ]);

    expect($permission)->toBeInstanceOf(Permission::class);
    expect($permission->permission)->toBe('create-post');
    expect($permission->descricao)->toBe('Can create posts');

    assertDatabaseHas('permissions', [
        'permission' => 'create-post',
        'descricao'  => 'Can create posts',
    ]);
});

test('permissão tem atributos preenchíveis', function (): void {
    $role       = Role::factory()->create();
    $permission = Permission::factory()->create([
        'permission' => 'edit-post',
        'descricao'  => 'Can edit posts',
        'role_id'    => $role->id,
    ]);

    expect($permission->permission)->toBe('edit-post');
    expect($permission->descricao)->toBe('Can edit posts');
    expect($permission->role_id)->toBe($role->id);
});

test('permissão tem relacionamento com usuários', function (): void {
    $role       = Role::factory()->create();
    $permission = Permission::factory()->create(['role_id' => $role->id]);
    $user       = User::factory()->create(['role_id' => $role->id]);

    $user->permissions()->attach($permission->id);

    expect($permission->fresh()->users)->toHaveCount(1);
    expect($permission->users->first())->toBeInstanceOf(User::class);
});

test('permissão pode pertencer a múltiplos usuários', function (): void {
    $role       = Role::factory()->create();
    $permission = Permission::factory()->create(['role_id' => $role->id]);
    $user1      = User::factory()->create(['role_id' => $role->id]);
    $user2      = User::factory()->create(['role_id' => $role->id]);
    $user3      = User::factory()->create(['role_id' => $role->id]);

    $user1->permissions()->attach($permission->id);
    $user2->permissions()->attach($permission->id);
    $user3->permissions()->attach($permission->id);

    expect($permission->fresh()->users)->toHaveCount(3);
});

test('permissão tem relacionamento com roles', function (): void {
    $role       = Role::factory()->create();
    $permission = Permission::factory()->create(['role_id' => $role->id]);

    $role->permissions()->attach($permission->id);

    expect($permission->fresh()->roles)->toHaveCount(1);
    expect($permission->roles->first())->toBeInstanceOf(Role::class);
});

test('permissão pode pertencer a múltiplas roles', function (): void {
    $permission = Permission::factory()->create();
    $role1      = Role::factory()->create();
    $role2      = Role::factory()->create();

    $role1->permissions()->attach($permission->id);
    $role2->permissions()->attach($permission->id);

    expect($permission->fresh()->roles)->toHaveCount(2);
});

test('permissão pertence a uma role', function (): void {
    $role       = Role::factory()->create();
    $permission = Permission::factory()->create(['role_id' => $role->id]);

    expect($permission->role_id)->toBe($role->id);
});
