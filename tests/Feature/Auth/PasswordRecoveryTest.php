<?php

declare(strict_types = 1);

use App\Livewire\Auth\Password\Recovery;
use App\Models\User;
use Illuminate\Support\Facades\Password;

test('password recovery page can be rendered', function (): void {
    Livewire::test(Recovery::class)
        ->assertOk();
});

test('password recovery requires email', function (): void {
    Livewire::test(Recovery::class)
        ->set('email', '')
        ->call('recuperacaoSenha')
        ->assertHasErrors(['email' => 'required']);
});

test('password recovery requires valid email format', function (): void {
    Livewire::test(Recovery::class)
        ->set('email', 'invalid-email')
        ->call('recuperacaoSenha')
        ->assertHasErrors(['email' => 'email']);
});

test('password recovery sends reset link', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);

    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => 'test@example.com'])
        ->andReturn(Password::RESET_LINK_SENT);

    $component = Livewire::test(Recovery::class)
        ->set('email', 'test@example.com')
        ->call('recuperacaoSenha')
        ->assertHasNoErrors();

    // Verify message was set (contains expected text)
    expect($component->message)->toContain('Email enviado');
});

test('password recovery displays success message', function (): void {
    $user = User::factory()->create();

    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn(Password::RESET_LINK_SENT);

    $component = Livewire::test(Recovery::class)
        ->set('email', $user->email)
        ->call('recuperacaoSenha');

    // Verify message was set (contains expected text)
    expect($component->message)->toContain('Email enviado');
});
