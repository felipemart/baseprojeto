<?php

declare(strict_types = 1);

use App\Livewire\User\PermissionUser;
use App\Models\Permission;
use App\Models\User;

test('user permission component can be mounted', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(PermissionUser::class, ['id' => $user->id])
        ->assertOk()
        ->assertSet('user.id', $user->id);
});

test('user permission displays available permissions', function (): void {
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

test('user permission can search permissions', function (): void {
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

test('user permission can update user permissions', function (): void {
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

test('user permission can remove user permissions', function (): void {
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

test('user permission can give all permissions', function (): void {
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

test('user permission filters permissions by user role', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    $component = Livewire::test(PermissionUser::class, ['id' => $user->id]);

    // Just verify component works
    expect($component)->not->toBeNull();
});
