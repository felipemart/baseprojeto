<?php

declare(strict_types = 1);

use App\Livewire\Auth\Password\Recovery;
use App\Models\User;
use Illuminate\Support\Facades\Password;

test('página de recuperação de senha pode ser renderizada', function (): void {
    Livewire::test(Recovery::class)
        ->assertOk();
});

test('recuperação de senha requer email', function (): void {
    Livewire::test(Recovery::class)
        ->set('email', '')
        ->call('recuperacaoSenha')
        ->assertHasErrors(['email' => 'required']);
});

test('recuperação de senha requer formato de email válido', function (): void {
    Livewire::test(Recovery::class)
        ->set('email', 'invalid-email')
        ->call('recuperacaoSenha')
        ->assertHasErrors(['email' => 'email']);
});

test('recuperação de senha envia link de redefinição', function (): void {
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

test('recuperação de senha exibe mensagem de sucesso', function (): void {
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
