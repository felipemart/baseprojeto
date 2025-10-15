<?php

declare(strict_types = 1);

use App\Notifications\EmailRecuperacaoSenha;
use Illuminate\Notifications\Messages\MailMessage;
use Mockery as m;

describe('EmailRecuperacaoSenha', function () {
    afterEach(function () {
        m::close();
        EmailRecuperacaoSenha::$createUrlCallback = null;
    });

    it('deve armazenar o token', function () {
        $token = 'test-token-456';
        $notification = new EmailRecuperacaoSenha($token);

        expect($notification->token)->toBe($token);
    });

    it('deve usar o canal mail', function () {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable = m::mock('stdClass');

        expect($notification->via($notifiable))->toBe(['mail']);
    });

    it('deve retornar uma MailMessage', function () {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail)->toBeInstanceOf(MailMessage::class);
    });

    it('deve ter o subject correto', function () {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->subject)->toBe('Notificação de redefinição de senha');
    });

    it('deve ter greeting correto', function () {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->greeting)->toBe('Redefinição de senha');
    });

    it('deve ter action button', function () {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionText)->toBe('Redefinir senha');
        expect($mail->actionUrl)->not->toBeNull();
    });

    it('deve mencionar tempo de expiração', function () {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        // Verificar se existe uma linha mencionando expiração
        $allLines = array_merge($mail->introLines, $mail->outroLines);
        $hasExpirationLine = false;
        foreach ($allLines as $line) {
            if (str_contains($line, 'expirar') || str_contains($line, 'minutos')) {
                $hasExpirationLine = true;
                break;
            }
        }

        expect($hasExpirationLine)->toBeTrue();
    });

    it('toArray deve retornar array vazio', function () {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable = m::mock('stdClass');

        expect($notification->toArray($notifiable))->toBe([]);
    });

    it('deve usar callback customizado se fornecido', function () {
        EmailRecuperacaoSenha::$createUrlCallback = function ($notifiable, $token) {
            return 'https://custom-reset.com/' . $token;
        };

        $notification = new EmailRecuperacaoSenha('test-token');
        $notifiable = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionUrl)->toBe('https://custom-reset.com/test-token');
    });
});

