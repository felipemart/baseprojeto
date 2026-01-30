<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Role Model', function (): void {
    test('pode criar uma role', function (): void {
        $role = Role::factory()->create([
            'name' => 'Administrator',
        ]);

        expect($role->name)->toBe('Administrator');
    });

    test('possui atributos fillable corretos', function (): void {
        $fillable = ['name'];
        $role     = new Role();

        expect($role->getFillable())->toBe($fillable);
    });

    test('pode ter múltiplos usuários', function (): void {
        $role  = Role::factory()->create();
        $user1 = User::factory()->create(['role_id' => $role->id]);
        $user2 = User::factory()->create(['role_id' => $role->id]);

        expect($role->users)->toHaveCount(2);
    });

    test('pode ter múltiplas permissões', function (): void {
        $role        = Role::factory()->create();
        $permission1 = Permission::factory()->create();
        $permission2 = Permission::factory()->create();

        $role->permissions()->attach([$permission1->id, $permission2->id]);

        expect($role->permissions)->toHaveCount(2);
    });

    test('relacionamento users retorna instância HasMany', function (): void {
        $role = new Role();

        expect($role->users())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\HasMany::class
        );
    });

    test('relacionamento permissions retorna instância BelongsToMany', function (): void {
        $role = new Role();

        expect($role->permissions())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class
        );
    });

    test('pode sincronizar permissões', function (): void {
        $role        = Role::factory()->create();
        $permission1 = Permission::factory()->create();
        $permission2 = Permission::factory()->create();
        $permission3 = Permission::factory()->create();

        $role->permissions()->sync([$permission1->id, $permission2->id]);
        expect($role->permissions)->toHaveCount(2);

        $role->permissions()->sync([$permission3->id]);
        expect($role->fresh()->permissions)->toHaveCount(1);
    });

    test('nome da role é único', function (): void {
        Role::factory()->create(['name' => 'Unique Role']);

        expect(fn () => Role::create(['name' => 'Unique Role']))
            ->toThrow(Exception::class);
    });

    test('pode desanexar permissões', function (): void {
        $role       = Role::factory()->create();
        $permission = Permission::factory()->create();

        $role->permissions()->attach($permission->id);
        expect($role->permissions)->toHaveCount(1);

        $role->permissions()->detach($permission->id);
        expect($role->fresh()->permissions)->toHaveCount(0);
    });
});
