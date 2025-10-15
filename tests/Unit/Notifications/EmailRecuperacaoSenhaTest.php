<?php

declare(strict_types = 1);

use App\Notifications\EmailRecuperacaoSenha;
use Illuminate\Notifications\Messages\MailMessage;
use Mockery as m;

describe('EmailRecuperacaoSenha', function (): void {
    afterEach(function (): void {
        m::close();
        EmailRecuperacaoSenha::$createUrlCallback = null;
    });

    it('deve armazenar o token', function (): void {
        $token        = 'test-token-456';
        $notification = new EmailRecuperacaoSenha($token);

        expect($notification->token)->toBe($token);
    });

    it('deve usar o canal mail', function (): void {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable   = m::mock('stdClass');

        expect($notification->via($notifiable))->toBe(['mail']);
    });

    it('deve retornar uma MailMessage', function (): void {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail)->toBeInstanceOf(MailMessage::class);
    });

    it('deve ter o subject correto', function (): void {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->subject)->toBe('Notificação de redefinição de senha');
    });

    it('deve ter greeting correto', function (): void {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->greeting)->toBe('Redefinição de senha');
    });

    it('deve ter action button', function (): void {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionText)->toBe('Redefinir senha');
        expect($mail->actionUrl)->not->toBeNull();
    });

    it('deve mencionar tempo de expiração', function (): void {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable   = m::mock('stdClass');
        $notifiable->shouldReceive('getEmailForPasswordReset')->andReturn('test@example.com');

        $mail = $notification->toMail($notifiable);

        // Verificar se existe uma linha mencionando expiração
        $allLines          = array_merge($mail->introLines, $mail->outroLines);
        $hasExpirationLine = false;

        foreach ($allLines as $line) {
            if (str_contains($line, 'expirar') || str_contains($line, 'minutos')) {
                $hasExpirationLine = true;

                break;
            }
        }

        expect($hasExpirationLine)->toBeTrue();
    });

    it('toArray deve retornar array vazio', function (): void {
        $notification = new EmailRecuperacaoSenha('token');
        $notifiable   = m::mock('stdClass');

        expect($notification->toArray($notifiable))->toBe([]);
    });

    it('deve usar callback customizado se fornecido', function (): void {
        EmailRecuperacaoSenha::$createUrlCallback = (fn ($notifiable, string $token): string => 'https://custom-reset.com/' . $token);

        $notification = new EmailRecuperacaoSenha('test-token');
        $notifiable   = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->actionUrl)->toBe('https://custom-reset.com/test-token');
    });
});
