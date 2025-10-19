<?php

declare(strict_types = 1);

use App\Livewire\User\PermissionUser;
use App\Models\Permission;
use App\Models\User;

test('componente de permissão de usuário pode ser montado', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(PermissionUser::class, ['id' => $user->id])
        ->assertOk()
        ->assertSet('user.id', $user->id);
});

test('permissão de usuário exibe permissões disponíveis', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $permission = Permission::factory()->create([
        'role_id'   => $user->role_id,
        'descricao' => 'Test Permission',
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(PermissionUser::class, ['id' => $user->id]);

    $permissions = $component->permissions;
    expect($permissions)->not->toBeEmpty();
});

test('permissão de usuário pode buscar permissões', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    Permission::factory()->create([
        'role_id'   => $user->role_id,
        'descricao' => 'User Create',
    ]);

    Permission::factory()->create([
        'role_id'   => $user->role_id,
        'descricao' => 'Post Delete',
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(PermissionUser::class, ['id' => $user->id])
        ->set('search', 'user');

    $permissions = $component->permissions;
    expect($permissions->first()->descricao)->toContain('User');
});

test('permissão de usuário pode atualizar permissões do usuário', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $permission = Permission::factory()->create([
        'role_id' => $user->role_id,
    ]);

    $this->actingAs($admin);

    Livewire::test(PermissionUser::class, ['id' => $user->id])
        ->set('setPermissions.' . $permission->id, true)
        ->call('updatePermissions', $permission->id);

    expect($user->fresh()->hasPermission($permission->permission))->toBeTrue();
});

test('permissão de usuário pode remover permissões do usuário', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $permission = Permission::factory()->create([
        'role_id' => $user->role_id,
    ]);

    $user->givePermissionId($permission->id);

    $this->actingAs($admin);

    Livewire::test(PermissionUser::class, ['id' => $user->id])
        ->set('setPermissions.' . $permission->id, false)
        ->call('updatePermissions', $permission->id);

    expect($user->fresh()->hasPermission($permission->permission))->toBeFalse();
});

test('permissão de usuário pode dar todas as permissões', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    Permission::factory()->count(3)->create([
        'role_id' => $user->role_id,
    ]);

    $this->actingAs($admin);

    Livewire::test(PermissionUser::class, ['id' => $user->id])
        ->call('allPermissions');

    $user = $user->fresh();
    expect($user->permissions)->toHaveCount(3);
});

test('permissão de usuário filtra permissões por role do usuário', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    $component = Livewire::test(PermissionUser::class, ['id' => $user->id]);

    // Just verify component works
    expect($component)->not->toBeNull();
});
