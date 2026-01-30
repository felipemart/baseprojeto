<?php

declare(strict_types = 1);

use App\Notifications\EmailCriacaoSenha;
use Illuminate\Notifications\Messages\MailMessage;
use Mockery as m;

describe('EmailCriacaoSenha', function (): void {
    afterEach(function (): void {
        m::close();
        EmailCriacaoSenha::$createUrlCallback = null;
    });

    test('deve armazenar o token', function (): void {
        $token        = 'test-token-123';
        $notification = new EmailCriacaoSenha($token);

        expect($notification->token)->toBe($token);
    });

    test('deve usar o canal mail', function (): void {
        $notification = new EmailCriacaoSenha('token');
        $notifiable   = m::mock('stdClass');

        expect($notification->via($notifiable))->toBe(['mail']);
    });

    test('deve retornar uma MailMessage', function (): void {
        $notification = new EmailCriacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail)->toBeInstanceOf(MailMessage::class);
    });

    test('deve ter o subject correto', function (): void {
        $notification = new EmailCriacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->subject)->toBe('Notificação de criação de senha');
    });

    test('deve ter greeting correto', function (): void {
        $notification = new EmailCriacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->greeting)->toBe('Criação de senha');
    });

    test('deve ter action button', function (): void {
        $notification = new EmailCriacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionText)->toBe('Criar senha');
        expect($mail->actionUrl)->not->toBeNull();
    });

    test('toArray deve retornar array vazio', function (): void {
        $notification = new EmailCriacaoSenha('token');
        $notifiable   = m::mock('stdClass');

        expect($notification->toArray($notifiable))->toBe([]);
    });

    test('deve usar callback customizado se fornecido', function (): void {
        EmailCriacaoSenha::$createUrlCallback = (fn ($notifiable, string $token): string => 'https://custom-url.com/' . $token);

        $notification = new EmailCriacaoSenha('test-token');
        $notifiable   = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionUrl)->toBe('https://custom-url.com/test-token');
    });
});
