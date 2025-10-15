<?php

declare(strict_types = 1);

use App\Notifications\EmailCriacaoSenha;
use Illuminate\Notifications\Messages\MailMessage;
use Mockery as m;

describe('EmailCriacaoSenha', function () {
    afterEach(function () {
        m::close();
        EmailCriacaoSenha::$createUrlCallback = null;
    });

    it('deve armazenar o token', function () {
        $token = 'test-token-123';
        $notification = new EmailCriacaoSenha($token);

        expect($notification->token)->toBe($token);
    });

    it('deve usar o canal mail', function () {
        $notification = new EmailCriacaoSenha('token');
        $notifiable = m::mock('stdClass');

        expect($notification->via($notifiable))->toBe(['mail']);
    });

    it('deve retornar uma MailMessage', function () {
        $notification = new EmailCriacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail)->toBeInstanceOf(MailMessage::class);
    });

    it('deve ter o subject correto', function () {
        $notification = new EmailCriacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->subject)->toBe('Notificação de criação de senha');
    });

    it('deve ter greeting correto', function () {
        $notification = new EmailCriacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->greeting)->toBe('Criação de senha');
    });

    it('deve ter action button', function () {
        $notification = new EmailCriacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionText)->toBe('Criar senha');
        expect($mail->actionUrl)->not->toBeNull();
    });

    it('toArray deve retornar array vazio', function () {
        $notification = new EmailCriacaoSenha('token');
        $notifiable = m::mock('stdClass');

        expect($notification->toArray($notifiable))->toBe([]);
    });

    it('deve usar callback customizado se fornecido', function () {
        EmailCriacaoSenha::$createUrlCallback = function ($notifiable, $token) {
            return 'https://custom-url.com/' . $token;
        };

        $notification = new EmailCriacaoSenha('test-token');
        $notifiable = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionUrl)->toBe('https://custom-url.com/test-token');
    });
});

