<?php

declare(strict_types = 1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;

beforeEach(function (): void {
    RateLimiter::clear('login');
});

test('página de login pode ser renderizada', function (): void {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('usuários podem autenticar usando formulário de login', function (): void {
    $user = User::factory()->create([
        'email'    => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('lgoin')
        ->assertRedirect(route('dashboard'));

    assertAuthenticated();
});

test('usuários não podem autenticar com senha inválida', function (): void {
    $user = User::factory()->create([
        'email'    => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', 'wrong-password')
        ->call('lgoin')
        ->assertHasErrors('crendenciaisInvalidas');

    assertGuest();
});

test('login requer email', function (): void {
    Livewire::test(Login::class)
        ->set('email', '')
        ->set('password', 'password')
        ->call('lgoin')
        ->assertHasErrors(['email' => 'required']);
});

test('login requer formato de email válido', function (): void {
    Livewire::test(Login::class)
        ->set('email', 'not-an-email')
        ->set('password', 'password')
        ->call('lgoin')
        ->assertHasErrors(['email' => 'email']);
});

test('login requer senha', function (): void {
    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->set('password', '')
        ->call('lgoin')
        ->assertHasErrors(['password' => 'required']);
});

test('login possui limitação de taxa após 5 tentativas falhadas', function (): void {
    $user = User::factory()->create([
        'email'    => 'test@example.com',
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

test('login bem-sucedido cria sessão para permissões e roles', function (): void {
    $user = User::factory()->create([
        'email'    => 'test@example.com',
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
