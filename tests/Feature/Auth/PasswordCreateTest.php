<?php

declare(strict_types = 1);

use App\Livewire\Auth\Password\Create;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

use function Pest\Laravel\get;

test('página de criação de senha pode ser renderizada com token válido', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    get(route('password.create', ['token' => $token, 'email' => $user->email]))
        ->assertSuccessful();
});

test('criação de senha redireciona com token inválido', function (): void {
    $user = User::factory()->create();

    Livewire::test(Create::class, ['token' => 'invalid-token', 'email' => $user->email])
        ->assertRedirect(route('login'));
});

test('criação de senha requer senha', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '')
        ->call('criarSenha')
        ->assertHasErrors(['password' => 'required']);
});

test('criação de senha requer confirmação de senha', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'differentpassword')
        ->call('criarSenha')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('criação de senha requer mínimo 8 caracteres', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '1234567')
        ->set('password_confirmation', '1234567')
        ->call('criarSenha')
        ->assertHasErrors(['password' => 'min']);
});

test('criação de senha requer email válido', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('email', 'invalid-email')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('criarSenha')
        ->assertHasErrors(['email' => 'email']);
});

test('pode criar senha com sucesso', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Create::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('criarSenha')
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Senha criada com sucesso.');
});

test('propriedade computed obfuscarEmail funciona', function (): void {
    $user  = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $component = Livewire::test(Create::class, ['token' => $token, 'email' => $user->email]);

    expect($component->obfuscarEmail)->toContain('*');
    expect($component->obfuscarEmail)->toContain('@example.com');
});
