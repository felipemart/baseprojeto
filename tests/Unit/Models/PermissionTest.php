<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Permission Model', function (): void {
    test('pode criar uma permissão', function (): void {
        $permission = Permission::factory()->create([
            'permission' => 'edit-posts',
            'descricao'  => 'Pode editar posts',
        ]);

        expect($permission->permission)->toBe('edit-posts');
        expect($permission->descricao)->toBe('Pode editar posts');
    });

    test('possui atributos fillable corretos', function (): void {
        $fillable   = ['permission', 'descricao', 'role_id'];
        $permission = new Permission();

        expect($permission->getFillable())->toBe($fillable);
    });

    test('pode pertencer a múltiplos usuários', function (): void {
        $permission = Permission::factory()->create();
        $user1      = User::factory()->create();
        $user2      = User::factory()->create();

        $permission->users()->attach([$user1->id, $user2->id]);

        expect($permission->users)->toHaveCount(2);
    });

    test('pode pertencer a múltiplas roles', function (): void {
        $permission = Permission::factory()->create();
        $role1      = Role::factory()->create();
        $role2      = Role::factory()->create();

        $permission->roles()->attach([$role1->id, $role2->id]);

        expect($permission->roles)->toHaveCount(2);
    });

    test('relacionamento users retorna instância BelongsToMany', function (): void {
        $permission = new Permission();

        expect($permission->users())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class
        );
    });

    test('relacionamento roles retorna instância BelongsToMany', function (): void {
        $permission = new Permission();

        expect($permission->roles())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class
        );
    });

    test('pode criar permissão com role_id', function (): void {
        $permission = Permission::factory()->create([
            'permission' => 'view-dashboard',
            'descricao'  => 'Visualizar dashboard',
            'role_id'    => 1,
        ]);

        expect($permission->role_id)->toBe(1);
    });

    test('pode sincronizar usuários', function (): void {
        $permission = Permission::factory()->create();
        $user1      = User::factory()->create();
        $user2      = User::factory()->create();

        $permission->users()->sync([$user1->id, $user2->id]);

        expect($permission->users)->toHaveCount(2);
    });
});
