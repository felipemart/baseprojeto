<?php

declare(strict_types = 1);

use App\Livewire\Auth\Password\Create;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

use function Pest\Laravel\{actingAs, assertDatabaseHas, get};

it('password create page can be rendered with valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    get(route('password.create', ['token' => $token, 'email' => $user->email]))
        ->assertSuccessful();
});

it('password create redirects with invalid token', function () {
    $user = User::factory()->create();
    
    Livewire::test(Create::class, ['token' => 'invalid-token', 'email' => $user->email])
        ->assertRedirect(route('login'));
});

it('password create requires password', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '')
        ->call('criarSenha')
        ->assertHasErrors(['password' => 'required']);
});

it('password create requires password confirmation', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'differentpassword')
        ->call('criarSenha')
        ->assertHasErrors(['password' => 'confirmed']);
});

it('password create requires minimum 8 characters', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '1234567')
        ->set('password_confirmation', '1234567')
        ->call('criarSenha')
        ->assertHasErrors(['password' => 'min']);
});

it('password create requires valid email', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('email', 'invalid-email')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('criarSenha')
        ->assertHasErrors(['email' => 'email']);
});

it('can create password successfully', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('criarSenha')
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Senha criada com sucesso.');
});

it('obfuscar email computed property works', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $component = Livewire::test(Create::class, ['token' => $token, 'email' => $user->email]);
    
    expect($component->obfuscarEmail)->toContain('*');
    expect($component->obfuscarEmail)->toContain('@example.com');
});


