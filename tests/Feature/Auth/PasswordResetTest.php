<?php

declare(strict_types = 1);

use App\Livewire\Auth\Password\Reset;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('password reset page can be rendered with valid token', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertSuccessful();
});

it('password reset redirects with invalid token', function (): void {
    $user = User::factory()->create();

    Livewire::test(Reset::class, ['token' => 'invalid-token', 'email' => $user->email])
        ->assertRedirect(route('login'));
});

it('password reset requires password', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '')
        ->call('resetarSenha')
        ->assertHasErrors(['password' => 'required']);
});

it('password reset requires password confirmation', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'differentpassword')
        ->call('resetarSenha')
        ->assertHasErrors(['password' => 'confirmed']);
});

it('password reset requires minimum 8 characters', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', '1234567')
        ->set('password_confirmation', '1234567')
        ->call('resetarSenha')
        ->assertHasErrors(['password' => 'min']);
});

it('password reset requires valid email', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('email', 'invalid-email')
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('resetarSenha')
        ->assertHasErrors(['email' => 'email']);
});

it('can reset password successfully', function (): void {
    $user  = User::factory()->create();
    $token = Password::createToken($user);

    Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email])
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('resetarSenha')
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Senha resetada com sucesso.');
});

it('obfuscar email computed property works in reset', function (): void {
    $user  = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $component = Livewire::test(Reset::class, ['token' => $token, 'email' => $user->email]);

    expect($component->obfuscarEmail)->toContain('*');
    expect($component->obfuscarEmail)->toContain('@example.com');
});
