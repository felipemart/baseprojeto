<?php

declare(strict_types = 1);

use App\Livewire\User\Delete;
use App\Models\User;

test('user delete component can be rendered', function (): void {
    $admin = createAdminWithSession();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->assertOk();
});

test('modal opens when user deletion event is dispatched', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Delete::class)
        ->dispatch('user.deletion', userId: $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});

test('user cannot delete themselves', function (): void {
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

test('user delete requires confirmation', function (): void {
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

test('user delete requires correct confirmation text', function (): void {
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

test('admin can delete user with correct confirmation', function (): void {
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

test('deleted user has deleted_by set', function (): void {
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
