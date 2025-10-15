<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

test('user can be given a permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->role_id = $role->id;
    $user->save();

    $user->givePermission('create-post');

    expect($user->hasPermission('create-post'))->toBeTrue();
    assertDatabaseHas('permissions', [
        'permission' => 'create-post',
        'role_id' => $role->id,
    ]);
});

test('user can check if has a permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->role_id = $role->id;
    $user->save();

    $user->givePermission('edit-post');

    expect($user->hasPermission('edit-post'))->toBeTrue();
    expect($user->hasPermission('delete-post'))->toBeFalse();
});

test('user can check multiple permissions', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->role_id = $role->id;
    $user->save();

    $user->givePermission('create-post');
    $user->givePermission('edit-post');

    expect($user->hasPermission(['create-post', 'edit-post']))->toBeTrue();
    expect($user->hasPermission(['delete-post', 'archive-post']))->toBeFalse();
    expect($user->hasPermission(['create-post', 'delete-post']))->toBeTrue(); // Tem pelo menos uma
});

test('permission session is created when permission is assigned', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->role_id = $role->id;
    $user->save();

    $this->actingAs($user);

    $user->givePermission('create-post');

    expect(session()->has("user:{$user->id}.permissions"))->toBeTrue();
});

test('permission can be given by id', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->role_id = $role->id;
    $user->save();

    $permission = Permission::factory()->create([
        'permission' => 'test-permission',
        'role_id' => $role->id,
    ]);

    $user->givePermissionId($permission->id);

    expect($user->permissions->contains($permission))->toBeTrue();
});

test('role can have multiple permissions', function () {
    $role = Role::factory()->create(['name' => 'Admin']);
    $permission1 = Permission::factory()->create(['permission' => 'create-post', 'role_id' => $role->id]);
    $permission2 = Permission::factory()->create(['permission' => 'edit-post', 'role_id' => $role->id]);

    $role->permissions()->attach([$permission1->id, $permission2->id]);

    expect($role->permissions)->toHaveCount(2);
});
