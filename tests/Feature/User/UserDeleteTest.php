<?php

declare(strict_types = 1);

use App\Livewire\User\Delete;
use App\Models\User;

test('componente de exclusão de usuário pode ser renderizado', function (): void {
    $admin = createAdminWithSession();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->assertOk();
});

test('modal abre quando evento de exclusão de usuário é disparado', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->dispatch('user.deletion', userId: $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});

test('usuário não pode excluir a si mesmo', function (): void {
    $admin = createAdminWithSession();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->set('user', $admin)
        ->set('modal', true)
        ->set('confirmDestroy_confirmation', 'DELETAR')
        ->call('destroy')
        ->assertHasErrors('confirmDestroy');

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
});

test('exclusão de usuário requer confirmação', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmDestroy_confirmation', '')
        ->call('destroy')
        ->assertHasErrors('confirmDestroy');
});

test('exclusão de usuário requer texto de confirmação correto', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmDestroy_confirmation', 'DELETE')
        ->call('destroy')
        ->assertHasErrors('confirmDestroy');
});

test('admin pode excluir usuário com confirmação correta', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmDestroy', 'DELETAR')
        ->set('confirmDestroy_confirmation', 'DELETAR')
        ->call('destroy')
        ->assertDispatched('user.deleted');

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('usuário excluído tem deleted_by definido', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmDestroy', 'DELETAR')
        ->set('confirmDestroy_confirmation', 'DELETAR')
        ->call('destroy');

    $user->refresh();
    expect($user->deleted_by)->toBe($admin->id);
});
