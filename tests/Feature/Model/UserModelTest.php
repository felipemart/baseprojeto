<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

test('usuário pode ser criado', function (): void {
    $user = User::factory()->create([
        'name'  => 'Test User',
        'email' => 'test@example.com',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});

test('senha do usuário é hash', function (): void {
    $user = User::factory()->create([
        'password' => 'plain-password',
    ]);

    expect($user->password)->not->toBe('plain-password');
    expect(Hash::check('plain-password', $user->password))->toBeTrue();
});

test('usuário tem atributos preenchíveis', function (): void {
    $user = User::factory()->create([
        'name'     => 'Test',
        'email'    => 'test@example.com',
        'password' => 'password',
    ]);

    expect($user->name)->toBe('Test');
    expect($user->email)->toBe('test@example.com');
});

test('senha do usuário é ocultada em arrays', function (): void {
    $user = User::factory()->create();

    $array = $user->toArray();

    expect($array)->not->toHaveKey('password');
    expect($array)->not->toHaveKey('remember_token');
});

test('usuário tem soft deletes', function (): void {
    $user = User::factory()->create();

    $user->delete();

    assertDatabaseHas('users', [
        'id' => $user->id,
    ]);

    expect($user->deleted_at)->not->toBeNull();
    expect(User::find($user->id))->toBeNull();
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

test('usuário pode ser deletado permanentemente', function (): void {
    $user   = User::factory()->create();
    $userId = $user->id;

    $user->forceDelete();

    expect(User::withTrashed()->find($userId))->toBeNull();
});

test('usuário tem relacionamento com role', function (): void {
    $user = User::factory()->create();
    $role = Role::factory()->create();

    $user->role_id = $role->id;
    $user->save();

    expect($user->role)->toBeInstanceOf(Role::class);
    expect($user->role->id)->toBe($role->id);
});

test('usuário tem relacionamento com permissões', function (): void {
    $user          = User::factory()->create();
    $role          = Role::factory()->create();
    $user->role_id = $role->id;
    $user->save();

    $permission = Permission::factory()->create(['role_id' => $role->id]);
    $user->permissions()->attach($permission->id);

    expect($user->permissions)->toHaveCount(1);
    expect($user->permissions->first())->toBeInstanceOf(Permission::class);
});

test('usuário tem relacionamento deleted_by', function (): void {
    $admin = User::factory()->create();
    $user  = User::factory()->create();

    $user->deleted_by = $admin->id;
    $user->save();

    expect($user->deletedBy)->toBeInstanceOf(User::class);
    expect($user->deletedBy->id)->toBe($admin->id);
});

test('usuário tem relacionamento restored_by', function (): void {
    $admin = User::factory()->create();
    $user  = User::factory()->create();

    $user->restored_by = $admin->id;
    $user->save();

    expect($user->restoredBy)->toBeInstanceOf(User::class);
    expect($user->restoredBy->id)->toBe($admin->id);
});

test('get key permissions retorna formato correto', function (): void {
    $user = User::factory()->create();

    $key = $user->getKeyPermissions();

    expect($key)->toBe("user:{$user->id}:permissions");
});

test('get key role retorna formato correto', function (): void {
    $user = User::factory()->create();

    $key = $user->getKeyRole();

    expect($key)->toBe("user:{$user->id}:roles");
});

test('email_verified_at é convertido para datetime', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(DateTimeInterface::class);
});
