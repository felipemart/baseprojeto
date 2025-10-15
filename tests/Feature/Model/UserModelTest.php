<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

test('user can be created', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});

test('user password is hashed', function () {
    $user = User::factory()->create([
        'password' => 'plain-password',
    ]);

    expect($user->password)->not->toBe('plain-password');
    expect(\Hash::check('plain-password', $user->password))->toBeTrue();
});

test('user has fillable attributes', function () {
    $user = User::factory()->create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    expect($user->name)->toBe('Test');
    expect($user->email)->toBe('test@example.com');
});

test('user password is hidden from arrays', function () {
    $user = User::factory()->create();

    $array = $user->toArray();

    expect($array)->not->toHaveKey('password');
    expect($array)->not->toHaveKey('remember_token');
});

test('user has soft deletes', function () {
    $user = User::factory()->create();

    $user->delete();

    assertDatabaseHas('users', [
        'id' => $user->id,
    ]);

    expect($user->deleted_at)->not->toBeNull();
    expect(User::find($user->id))->toBeNull();
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

test('user can be force deleted', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $user->forceDelete();

    expect(User::withTrashed()->find($userId))->toBeNull();
});

test('user has role relationship', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();

    $user->role_id = $role->id;
    $user->save();

    expect($user->role)->toBeInstanceOf(Role::class);
    expect($user->role->id)->toBe($role->id);
});

test('user has permissions relationship', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->role_id = $role->id;
    $user->save();

    $permission = Permission::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    expect($user->permissions)->toHaveCount(1);
    expect($user->permissions->first())->toBeInstanceOf(Permission::class);
});

test('user has deleted_by relationship', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();

    $user->deleted_by = $admin->id;
    $user->save();

    expect($user->deletedBy)->toBeInstanceOf(User::class);
    expect($user->deletedBy->id)->toBe($admin->id);
});

test('user has restored_by relationship', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();

    $user->restored_by = $admin->id;
    $user->save();

    expect($user->restoredBy)->toBeInstanceOf(User::class);
    expect($user->restoredBy->id)->toBe($admin->id);
});

test('user get key permissions returns correct format', function () {
    $user = User::factory()->create();

    $key = $user->getKeyPermissions();

    expect($key)->toBe("user:{$user->id}:permissions");
});

test('user get key role returns correct format', function () {
    $user = User::factory()->create();

    $key = $user->getKeyRole();

    expect($key)->toBe("user:{$user->id}:roles");
});

test('user email_verified_at is cast to datetime', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(\DateTimeInterface::class);
});

