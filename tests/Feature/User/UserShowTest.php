<?php

declare(strict_types = 1);

use App\Livewire\User\Show;
use App\Models\User;

test('componente de visualização de usuário pode ser renderizado', function (): void {
    $admin = createAdminWithSession();

    $this->actingAs($admin);

    Livewire::test(Show::class)
        ->assertOk();
});

test('modal abre quando evento de visualização de usuário é disparado', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(Show::class)
        ->dispatch('user.showing', userId: $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});

test('visualização de usuário pode carregar detalhes do usuário', function (): void {
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

test('visualização de usuário pode carregar usuários excluídos', function (): void {
    $admin = createAdminWithSession();
    $user  = User::factory()->create();
    $user->delete();

    $this->actingAs($admin);

    Livewire::test(Show::class)
        ->call('loadUser', $user->id)
        ->assertSet('modal', true)
        ->assertSet('user.id', $user->id);
});
