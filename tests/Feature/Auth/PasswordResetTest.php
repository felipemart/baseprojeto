<?php

declare(strict_types = 1);

use App\Livewire\Auth\Password\Reset;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

use function Pest\Laravel\get;

test('página de redefinição de senha pode ser renderizada com token válido', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertSuccessful();
});

test('redefinição de senha redireciona com token inválido', function (): void {
    $user = User::factory()->create();

    Livewire::test(Reset::class, ['token' => 'invalid-token', 'email' => $user->email])
        ->assertRedirect(route('login'));
});

test('redefinição de senha requer senha', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '')
        ->call('resetarSenha')
        ->assertHasErrors(['password' => 'required']);
});

test('redefinição de senha requer confirmação de senha', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'differentpassword')
        ->call('resetarSenha')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('redefinição de senha requer mínimo 8 caracteres', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '1234567')
        ->set('password_confirmation', '1234567')
        ->call('resetarSenha')
        ->assertHasErrors(['password' => 'min']);
});

test('redefinição de senha requer email válido', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('email', 'invalid-email')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('resetarSenha')
        ->assertHasErrors(['email' => 'email']);
});

test('pode redefinir senha com sucesso', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('resetarSenha')
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Senha resetada com sucesso.');
});

test('propriedade computed obfuscarEmail funciona em redefinição', function (): void {
    $user  = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $component = Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email]);

    expect($component->obfuscarEmail)->toContain('*');
    expect($component->obfuscarEmail)->toContain('@example.com');
});
