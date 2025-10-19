<?php

declare(strict_types = 1);

use App\Models\User;
use App\Notifications\EmailRecuperacaoSenha;
use Illuminate\Support\Facades\Notification;

test('usuário pode receber notificação de redefinição de senha', function (): void {
    Notification::fake();

    $user  = User::factory()->create(['email' => 'test@example.com']);
    $token = 'fake-token';

    $user->sendPasswordResetNotification($token);

    Notification::assertSentTo(
        $user,
        EmailRecuperacaoSenha::class,
        fn ($notification, $channels): bool => $notification->token === $token
    );
});

test('notificação de redefinição de senha tem token correto', function (): void {
    $token        = 'test-token-123';
    $notification = new EmailRecuperacaoSenha($token);

    expect($notification->token)->toBe($token);
});

test('notificação de redefinição de senha usa canal de email', function (): void {
    Notification::fake();

    $user  = User::factory()->create();
    $token = 'test-token';

    $user->sendPasswordResetNotification($token);

    Notification::assertSentTo(
        $user,
        EmailRecuperacaoSenha::class,
        fn ($notification, $channels): bool => in_array('mail', $channels)
    );
});
