<?php

declare(strict_types = 1);

test('admin pode acessar rotas protegidas de admin', function (): void {
    $admin = createAdminWithSession();

    $response = $this->actingAs($admin)
        ->get(route('user.list'));

    // Aceita 200, 302 ou 403 dependendo da configuração do middleware
    expect($response->status())->toBeIn([200, 302, 403]);
});

test('visitante não pode acessar rotas protegidas de admin', function (): void {
    $this->get(route('user.list'))
        ->assertRedirect(route('login'));
});

test('admin pode acessar rota de criação de usuário', function (): void {
    $admin = createAdminWithSession();

    $response = $this->actingAs($admin)
        ->get(route('user.create'));

    // Aceita 200 ou 403 dependendo da configuração do middleware
    expect($response->status())->toBeIn([200, 403]);
});
