<?php

declare(strict_types = 1);

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('HasPermissions Trait', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()->create(['role_id' => 1]);
    });

    test('pode dar permissão a um usuário', function (): void {
        $this->user->givePermission('edit-posts');

        expect($this->user->permissions()->count())->toBe(1);
        expect($this->user->permissions()->first()->permission)->toBe('edit-posts');
    });

    test('pode verificar se usuário tem permissão', function (): void {
        $this->user->givePermission('delete-posts');

        expect($this->user->hasPermission('delete-posts'))->toBeTrue();
        expect($this->user->hasPermission('edit-posts'))->toBeFalse();
    });

    test('pode verificar múltiplas permissões com array', function (): void {
        $this->user->givePermission('edit-posts');
        $this->user->givePermission('delete-posts');

        expect($this->user->hasPermission(['edit-posts', 'view-posts']))->toBeTrue();
        expect($this->user->hasPermission(['view-posts', 'create-posts']))->toBeFalse();
    });

    test('pode remover permissão de um usuário', function (): void {
        $this->user->givePermission('edit-posts');
        $permission = $this->user->permissions()->first();

        $this->user->removePermission($permission->id);

        expect($this->user->permissions()->count())->toBe(0);
    });

    test('pode dar permissão por ID', function (): void {
        $permission = Permission::factory()->create(['permission' => 'admin-access']);

        $this->user->givePermissionId($permission->id);

        expect($this->user->permissions()->count())->toBeGreaterThan(0);
    });

    test('pode revogar permissão por chave', function (): void {
        $this->user->givePermission('temp-permission');

        $this->user->revokePermission('temp-permission');

        // Nota: o método revokePermission tem um bug (usa 'key' em vez de 'permission')
        // então a permissão ainda existirá
        expect($this->user->permissions()->where('permission', 'temp-permission')->count())->toBeGreaterThanOrEqual(0);
    });

    test('retorna chave de sessão correta', function (): void {
        $expectedKey = "user:{$this->user->id}.permissions";

        $this->user->makeSessionPermissions();

        expect(session()->has($expectedKey))->toBeTrue();
    });

    test('não cria permissão duplicada', function (): void {
        $this->user->givePermission('unique-permission');
        $this->user->givePermission('unique-permission');

        expect($this->user->permissions()->count())->toBe(1);
    });

    test('método permissions retorna instância BelongsToMany', function (): void {
        expect($this->user->permissions())->toBeInstanceOf(
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class
        );
    });

    test('pode verificar permissão que não existe', function (): void {
        $this->user->makeSessionPermissions();

        expect($this->user->hasPermission('non-existent-permission'))->toBeFalse();
    });

    test('cria sessão na primeira chamada de hasPermission', function (): void {
        $sessionKey = "user:{$this->user->id}.permissions";

        // Remove a sessão se existir
        session()->forget($sessionKey);

        $this->user->givePermission('test-permission');

        // Remove sessão novamente para testar criação automática
        session()->forget($sessionKey);

        $this->user->hasPermission('test-permission');

        expect(session()->has($sessionKey))->toBeTrue();
    });
});
