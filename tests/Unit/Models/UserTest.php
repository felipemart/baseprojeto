<?php

declare(strict_types = 1);

use App\Models\User;
use App\Notifications\EmailRecuperacaoSenha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('User Model', function () {
    it('pode criar um usuário', function () {
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ]);

        expect($user->name)->toBe('João Silva');
        expect($user->email)->toBe('joao@example.com');
    });

    it('senha é automaticamente hasheada', function () {
        $user = User::factory()->create([
            'password' => 'senha123',
        ]);

        expect($user->password)->not->toBe('senha123');
        expect(strlen($user->password))->toBeGreaterThan(20);
    });

    it('possui atributos fillable corretos', function () {
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

    it('esconde password e remember_token', function () {
        $user = new User();

        expect($user->getHidden())->toContain('password');
        expect($user->getHidden())->toContain('remember_token');
    });

    it('retorna chave de permissões correta', function () {
        $user = User::factory()->create();
        $expectedKey = "user:{$user->id}:permissions";

        expect($user->getKeyPermissions())->toBe($expectedKey);
    });

    it('retorna chave de role correta', function () {
        $user = User::factory()->create();
        $expectedKey = "user:{$user->id}:roles";

        expect($user->getKeyRole())->toBe($expectedKey);
    });

    it('pode ter relacionamento com restoredBy', function () {
        $admin = User::factory()->create();
        $user = User::factory()->create(['restored_by' => $admin->id]);

        expect($user->restoredBy)->not->toBeNull();
        expect($user->restoredBy->id)->toBe($admin->id);
    });

    it('pode ter relacionamento com deletedBy', function () {
        $admin = User::factory()->create();
        $user = User::factory()->create(['deleted_by' => $admin->id]);

        expect($user->deletedBy)->not->toBeNull();
        expect($user->deletedBy->id)->toBe($admin->id);
    });

    it('envia notificação customizada de recuperação de senha', function () {
        Notification::fake();

        $user = User::factory()->create();
        $token = 'test-token-123';

        $user->sendPasswordResetNotification($token);

        Notification::assertSentTo(
            $user,
            EmailRecuperacaoSenha::class,
            function ($notification) use ($token) {
                return true;
            }
        );
    });

    it('usa soft deletes', function () {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        expect(User::find($userId))->toBeNull();
        expect(User::withTrashed()->find($userId))->not->toBeNull();
    });

it('email_verified_at é cast para datetime', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(\DateTimeInterface::class);
});

it('role relationship works correctly', function () {
    $role = \App\Models\Role::factory()->create();
    $user = User::factory()->create(['role_id' => $role->id]);

    expect($user->role)->not->toBeNull();
    expect($user->role->id)->toBe($role->id);
});

it('permissions relationship works correctly', function () {
    $user = User::factory()->create();
    $permission = \App\Models\Permission::factory()->create();
    
    $user->permissions()->attach($permission->id);

    expect($user->permissions)->toHaveCount(1);
});

it('can update user data', function () {
    $user = User::factory()->create(['name' => 'Original Name']);
    
    $user->name = 'Updated Name';
    $user->save();

    expect($user->fresh()->name)->toBe('Updated Name');
});

it('fillable includes all expected fields', function () {
    $user = new User();
    $fillable = $user->getFillable();
    
    expect($fillable)->toContain('name');
    expect($fillable)->toContain('email');
    expect($fillable)->toContain('password');
    expect($fillable)->toContain('role_id');
});
});

