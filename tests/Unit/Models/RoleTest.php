<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Role Model', function () {
    it('pode criar uma role', function () {
        $role = Role::factory()->create([
            'name' => 'Administrator',
        ]);

        expect($role->name)->toBe('Administrator');
    });

    it('possui atributos fillable corretos', function () {
        $fillable = ['name'];
        $role = new Role();

        expect($role->getFillable())->toBe($fillable);
    });

    it('pode ter múltiplos usuários', function () {
        $role = Role::factory()->create();
        $user1 = User::factory()->create(['role_id' => $role->id]);
        $user2 = User::factory()->create(['role_id' => $role->id]);

        expect($role->users)->toHaveCount(2);
    });

    it('pode ter múltiplas permissões', function () {
        $role = Role::factory()->create();
        $permission1 = Permission::factory()->create();
        $permission2 = Permission::factory()->create();

        $role->permissions()->attach([$permission1->id, $permission2->id]);

        expect($role->permissions)->toHaveCount(2);
    });

    it('relacionamento users retorna instância HasMany', function () {
        $role = new Role();

        expect($role->users())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\HasMany::class
        );
    });

    it('relacionamento permissions retorna instância BelongsToMany', function () {
        $role = new Role();

        expect($role->permissions())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class
        );
    });

    it('pode sincronizar permissões', function () {
        $role = Role::factory()->create();
        $permission1 = Permission::factory()->create();
        $permission2 = Permission::factory()->create();
        $permission3 = Permission::factory()->create();

        $role->permissions()->sync([$permission1->id, $permission2->id]);
        expect($role->permissions)->toHaveCount(2);

        $role->permissions()->sync([$permission3->id]);
        expect($role->fresh()->permissions)->toHaveCount(1);
    });

    it('nome da role é único', function () {
        Role::factory()->create(['name' => 'Unique Role']);

        expect(fn () => Role::create(['name' => 'Unique Role']))
            ->toThrow(Exception::class);
    })->skip('Não há constraint de unicidade no banco');

    it('can detach permissions', function () {
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();
        
        $role->permissions()->attach($permission->id);
        expect($role->permissions)->toHaveCount(1);
        
        $role->permissions()->detach($permission->id);
        expect($role->fresh()->permissions)->toHaveCount(0);
    });
});

