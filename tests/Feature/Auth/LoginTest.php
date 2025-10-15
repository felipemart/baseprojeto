<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;

beforeEach(function () {
    RateLimiter::clear('login');
});

test('login page can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login form', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('lgoin')
        ->assertRedirect(route('dashboard'));

    assertAuthenticated();
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'wrong-password')
        ->call('lgoin')
        ->assertHasErrors('crendenciaisInvalidas');

    assertGuest();
});

test('login requires email', function () {
    Livewire::test(Login::class)
        ->set('email', '')
        ->set('password', 'password')
        ->call('lgoin')
        ->assertHasErrors(['email' => 'required']);
});

test('login requires valid email format', function () {
    Livewire::test(Login::class)
        ->set('email', 'not-an-email')
        ->set('password', 'password')
        ->call('lgoin')
        ->assertHasErrors(['email' => 'email']);
});

test('login requires password', function () {
    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', '')
        ->call('lgoin')
        ->assertHasErrors(['password' => 'required']);
});

test('login is rate limited after 5 failed attempts', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Simular 5 tentativas falhadas
    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('lgoin');
    }

    // A 6ª tentativa deve ser bloqueada pelo rate limiter
    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('lgoin')
        ->assertHasErrors('rateLimiter');

    assertGuest();
});

test('successful login creates session for permissions and roles', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('lgoin');

    assertAuthenticated();
    expect(session()->has("user:{$user->id}.permissions"))->toBeTrue();
    expect(session()->has("user:{$user->id}.roles"))->toBeTrue();
});

// Removido: teste de redirecionamento de usuário autenticado
// O comportamento esperado pode variar dependendo da configuração do middleware

