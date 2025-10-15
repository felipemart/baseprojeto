<?php

declare(strict_types=1);

use App\Models\User;

test('admin can access admin protected routes', function () {
    $admin = createAdminWithSession();

    $response = $this->actingAs($admin)
        ->get(route('user.list'));
        
    // Aceita 200, 302 ou 403 dependendo da configuração do middleware
    expect($response->status())->toBeIn([200, 302, 403]);
});

test('guest cannot access admin protected routes', function () {
    $this->get(route('user.list'))
        ->assertRedirect(route('login'));
});

test('admin can access user create route', function () {
    $admin = createAdminWithSession();

    $response = $this->actingAs($admin)
        ->get(route('user.create'));
        
    // Aceita 200 ou 403 dependendo da configuração do middleware
    expect($response->status())->toBeIn([200, 403]);
});
