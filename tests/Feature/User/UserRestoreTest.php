<?php

use App\Livewire\User\Restore;
use App\Models\User;

test('user restore component can be rendered', function () {
    $admin = createAdminWithSession();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->assertOk();
});

test('modal opens when user restoring event is dispatched', function () {
    $admin = createAdminWithSession();
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->dispatch('user.restoring', userId: $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});

test('user restore requires confirmation', function () {
    $admin = createAdminWithSession();
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmRestore_confirmation', '')
        ->call('restore')
        ->assertHasErrors('confirmRestore');
});

test('user restore requires correct confirmation text', function () {
    $admin = createAdminWithSession();
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Restore::class)
        ->set('user', $user)
        ->set('modal', true)
        ->set('confirmRestore_confirmation', 'RESTORE')
        ->call('restore')
        ->assertHasErrors('confirmRestore');
});

test('admin can restore deleted user with correct confirmation', function () {
    $admin = createAdminWithSession();
    $user = User::factory()->create();
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

test('restored user has restored_by set', function () {
    $admin = createAdminWithSession();
    $user = User::factory()->create();
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

test('user cannot restore themselves', function () {
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

