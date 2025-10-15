<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Permission Model', function () {
    it('pode criar uma permissão', function () {
        $permission = Permission::factory()->create([
            'permission' => 'edit-posts',
            'descricao' => 'Pode editar posts',
        ]);

        expect($permission->permission)->toBe('edit-posts');
        expect($permission->descricao)->toBe('Pode editar posts');
    });

    it('possui atributos fillable corretos', function () {
        $fillable = ['permission', 'descricao', 'role_id'];
        $permission = new Permission();

        expect($permission->getFillable())->toBe($fillable);
    });

    it('pode pertencer a múltiplos usuários', function () {
        $permission = Permission::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $permission->users()->attach([$user1->id, $user2->id]);

        expect($permission->users)->toHaveCount(2);
    });

    it('pode pertencer a múltiplas roles', function () {
        $permission = Permission::factory()->create();
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $permission->roles()->attach([$role1->id, $role2->id]);

        expect($permission->roles)->toHaveCount(2);
    });

    it('relacionamento users retorna instância BelongsToMany', function () {
        $permission = new Permission();

        expect($permission->users())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class
        );
    });

    it('relacionamento roles retorna instância BelongsToMany', function () {
        $permission = new Permission();

        expect($permission->roles())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class
        );
    });

    it('pode criar permissão com role_id', function () {
        $permission = Permission::factory()->create([
            'permission' => 'view-dashboard',
            'descricao' => 'Visualizar dashboard',
            'role_id' => 1,
        ]);

        expect($permission->role_id)->toBe(1);
    });

    it('can sync users', function () {
        $permission = Permission::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $permission->users()->sync([$user1->id, $user2->id]);
        
        expect($permission->users)->toHaveCount(2);
    });
});

