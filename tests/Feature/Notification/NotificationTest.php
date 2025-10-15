<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\EmailRecuperacaoSenha;
use Illuminate\Support\Facades\Notification;

test('user can receive password reset notification', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = 'fake-token';

    $user->sendPasswordResetNotification($token);

    Notification::assertSentTo(
        $user,
        EmailRecuperacaoSenha::class,
        function ($notification, $channels) use ($token) {
            return $notification->token === $token;
        }
    );
});

test('password reset notification has correct token', function () {
    $token = 'test-token-123';
    $notification = new EmailRecuperacaoSenha($token);

    expect($notification->token)->toBe($token);
});

test('password reset notification uses mail channel', function () {
    Notification::fake();

    $user = User::factory()->create();
    $token = 'test-token';

    $user->sendPasswordResetNotification($token);

    Notification::assertSentTo(
        $user,
        EmailRecuperacaoSenha::class,
        function ($notification, $channels) {
            return in_array('mail', $channels);
        }
    );
});

