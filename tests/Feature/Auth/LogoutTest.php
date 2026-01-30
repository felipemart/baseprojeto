<?php

declare(strict_types = 1);

use App\Models\User;

use function Pest\Laravel\assertGuest;

test('usuário autenticado pode fazer logout', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('logout'))
        ->assertRedirect(route('login'));

    assertGuest();
});

test('visitante não pode acessar logout', function (): void {
    $this->get(route('logout'))
        ->assertRedirect(route('login'));
});

test('logout limpa a sessão do usuário', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Criar dados de sessão
    session(['user_data' => 'test']);

    $this->get(route('logout'));

    assertGuest();
});
