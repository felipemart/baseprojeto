<?php

declare(strict_types = 1);

use App\Models\User;
use App\Notifications\EmailRecuperacaoSenha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('User Model', function (): void {
    test('pode criar um usuário', function (): void {
        $user = User::factory()->create([
            'name'  => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        expect($user->name)->toBe('João Silva');
        expect($user->email)->toBe('joao@example.com');
    });

    test('senha é automaticamente hasheada', function (): void {
        $user = User::factory()->create([
            'password' => 'senha123',
        ]);

        expect($user->password)->not->toBe('senha123');
        expect(strlen((string) $user->password))->toBeGreaterThan(20);
    });

    test('possui atributos fillable corretos', function (): void {
        $fillable = [
            'name',
            'email',
            'password',
            'restored_at',
            'restored_by',
            'deleted_by',
            'role_id',
        ];

        $user = new User();

        expect($user->getFillable())->toBe($fillable);
    });

    test('esconde password e remember_token', function (): void {
        $user = new User();

        expect($user->getHidden())->toContain('password');
        expect($user->getHidden())->toContain('remember_token');
    });

    test('retorna chave de permissões correta', function (): void {
        $user        = User::factory()->create();
        $expectedKey = "user:{$user->id}:permissions";

        expect($user->getKeyPermissions())->toBe($expectedKey);
    });

    test('retorna chave de role correta', function (): void {
        $user        = User::factory()->create();
        $expectedKey = "user:{$user->id}:roles";

        expect($user->getKeyRole())->toBe($expectedKey);
    });

    test('pode ter relacionamento com restoredBy', function (): void {
        $admin = User::factory()->create();
        $user  = User::factory()->create(['restored_by' => $admin->id]);

        expect($user->restoredBy)->not->toBeNull();
        expect($user->restoredBy->id)->toBe($admin->id);
    });

    test('pode ter relacionamento com deletedBy', function (): void {
        $admin = User::factory()->create();
        $user  = User::factory()->create(['deleted_by' => $admin->id]);

        expect($user->deletedBy)->not->toBeNull();
        expect($user->deletedBy->id)->toBe($admin->id);
    });

    test('envia notificação customizada de recuperação de senha', function (): void {
        Notification::fake();

        $user  = User::factory()->create();
        $token = 'test-token-123';

        $user->sendPasswordResetNotification($token);

        Notification::assertSentTo(
            $user,
            EmailRecuperacaoSenha::class,
            fn ($notification): true => true
        );
    });

    test('usa soft deletes', function (): void {
        $user   = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        expect(User::find($userId))->toBeNull();
        expect(User::withTrashed()->find($userId))->not->toBeNull();
    });

    test('email_verified_at é cast para datetime', function (): void {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        expect($user->email_verified_at)->toBeInstanceOf(DateTimeInterface::class);
    });

    test('relacionamento role funciona corretamente', function (): void {
        $role = App\Models\Role::factory()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        expect($user->role)->not->toBeNull();
        expect($user->role->id)->toBe($role->id);
    });

    test('relacionamento permissions funciona corretamente', function (): void {
        $user       = User::factory()->create();
        $permission = App\Models\Permission::factory()->create();

        $user->permissions()->attach($permission->id);

        expect($user->permissions)->toHaveCount(1);
    });

    test('pode atualizar dados do usuário', function (): void {
        $user = User::factory()->create(['name' => 'Original Name']);

        $user->name = 'Updated Name';
        $user->save();

        expect($user->fresh()->name)->toBe('Updated Name');
    });

    test('fillable inclui todos os campos esperados', function (): void {
        $user     = new User();
        $fillable = $user->getFillable();

        expect($fillable)->toContain('name');
        expect($fillable)->toContain('email');
        expect($fillable)->toContain('password');
        expect($fillable)->toContain('role_id');
    });
});
