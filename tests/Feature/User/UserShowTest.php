<?php

declare(strict_types = 1);

use App\Livewire\User\Show;
use App\Models\User;

test('user show component can be rendered', function (): void {
    $admin = createAdminWithSession();

    $this->actingAs($admin);

    Livewire::test(Show::class)
        ->assertOk();
});

test('modal opens when user showing event is dispatched', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Show::class)
        ->dispatch('user.showing', userId: $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});

test('user show can load user details', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create([
        'name'  => 'Test User',
        'email' => 'test@example.com',
    ]);

    $this->actingAs($admin);

    Livewire::test(Show::class)
        ->call('loadUser', $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.name', 'Test User')
        ->assertSet('user.email', 'test@example.com');
});

test('user show can load deleted users', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Show::class)
        ->call('loadUser', $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});
