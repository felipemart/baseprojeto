<?php

declare(strict_types=1);

use App\Livewire\Auth\Logout;
use App\Models\User;

use function Pest\Laravel\assertGuest;

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('logout'))
        ->assertRedirect(route('login'));

    assertGuest();
});

test('guest cannot access logout', function () {
    $this->get(route('logout'))
        ->assertRedirect(route('login'));
});

test('logout clears user session', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Criar dados de sessão
    session(['user_data' => 'test']);

    $this->get(route('logout'));

    assertGuest();
});

