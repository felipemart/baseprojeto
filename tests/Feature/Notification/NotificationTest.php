<?php

declare(strict_types = 1);

use App\Models\User;
use App\Notifications\EmailRecuperacaoSenha;
use Illuminate\Support\Facades\Notification;

test('user can receive password reset notification', function (): void {
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

test('password reset notification has correct token', function (): void {
    $token        = 'test-token-123';
    $notification = new EmailRecuperacaoSenha($token);

    expect($notification->token)->toBe($token);
});

test('password reset notification uses mail channel', function (): void {
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
