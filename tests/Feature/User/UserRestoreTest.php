<?php

declare(strict_types = 1);

use App\Livewire\User\Restore;
use App\Models\User;

test('componente de restauração de usuário pode ser renderizado', function (): void {
    $admin = createAdminWithSession();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->assertOk();
});

test('modal abre quando evento de restauração de usuário é disparado', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->dispatch('user.restoring', userId: $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});

test('restauração de usuário requer confirmação', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmRestore_confirmation', '')
        ->call('restore')
        ->assertHasErrors('confirmRestore');
});

test('restauração de usuário requer texto de confirmação correto', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmRestore_confirmation', 'RESTORE')
        ->call('restore')
        ->assertHasErrors('confirmRestore');
});

test('admin pode restaurar usuário excluído com confirmação correta', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmRestore', 'RESTAURAR')
        ->set('confirmRestore_confirmation', 'RESTAURAR')
        ->call('restore')
        ->assertDispatched('user.restored');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
});

test('usuário restaurado tem restored_by definido', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmRestore', 'RESTAURAR')
        ->set('confirmRestore_confirmation', 'RESTAURAR')
        ->call('restore');

    $user->refresh();
    expect($user->restored_by)->toBe($admin->id);
    expect($user->restored_at)->not->toBeNull();
});

test('usuário não pode restaurar a si mesmo', function (): void {
    $admin = createAdminWithSession();
    $admin->delete();

    // Need to re-authenticate as deleted user
    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->set('user', $admin)
        ->set('modal', true)
        ->set('confirmRestore', 'RESTAURAR')
        ->set('confirmRestore_confirmation', 'RESTAURAR')
        ->call('restore')
        ->assertHasErrors('confirmDestroy');
});
