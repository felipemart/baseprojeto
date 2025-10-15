<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

test('role can be created', function (): void {
    $role = Role::factory()->create(['name' => 'Admin']);

    expect($role)->toBeInstanceOf(Role::class);
    expect($role->name)->toBe('Admin');

    assertDatabaseHas('roles', [
        'name' => 'Admin',
    ]);
});

test('role has fillable name attribute', function (): void {
    $role = Role::factory()->create(['name' => 'Manager']);

    expect($role->name)->toBe('Manager');
});

test('role has users relationship', function (): void {
    $role = Role::factory()->create();
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($role->users)->toHaveCount(1);
    expect($role->users->first())->toBeInstanceOf(User::class);
    expect($role->users->first()->id)->toBe($user->id);
});

test('role can have multiple users', function (): void {
    $role  = Role::factory()->create();
    $user1 = User::factory()->create(['role_id' => $role->id]);
    $user2 = User::factory()->create(['role_id' => $role->id]);
    $user3 = User::factory()->create(['role_id' => $role->id]);

    expect($role->fresh()->users)->toHaveCount(3);
});

test('role has permissions relationship', function (): void {
    $role       = Role::factory()->create();
    $permission = Permission::factory()->create(['role_id' => $role->id]);

    $role->permissions()->attach($permission->id);

    expect($role->permissions)->toHaveCount(1);
    expect($role->permissions->first())->toBeInstanceOf(Permission::class);
});

test('role can have multiple permissions', function (): void {
    $role        = Role::factory()->create();
    $permission1 = Permission::factory()->create(['role_id' => $role->id]);
    $permission2 = Permission::factory()->create(['role_id' => $role->id]);
    $permission3 = Permission::factory()->create(['role_id' => $role->id]);

    $role->permissions()->attach([$permission1->id, $permission2->id, $permission3->id]);

    expect($role->fresh()->permissions)->toHaveCount(3);
});

test('deleting role does not delete users', function (): void {
    $role = Role::factory()->create();
    $user = User::factory()->create(['role_id' => $role->id]);

    $role->delete();

    expect(User::find($user->id))->not->toBeNull();
});
