<?php

declare(strict_types = 1);

use App\Notifications\BemVindoNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Mockery as m;

describe('BemVindoNotification', function (): void {
    afterEach(function (): void {
        m::close();
    });

    test('deve usar o canal mail', function (): void {
        $notification = new BemVindoNotification();
        $notifiable   = m::mock('stdClass');

        expect($notification->via($notifiable))->toBe(['mail']);
    });

    test('deve retornar uma MailMessage', function (): void {
        $notification = new BemVindoNotification();
        $notifiable   = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail)->toBeInstanceOf(MailMessage::class);
    });

    test('deve ter o subject correto', function (): void {
        $notification = new BemVindoNotification();
        $notifiable   = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->subject)->toBe('Email de boas vindas');
    });

    test('deve ter greeting de boas vindas', function (): void {
        $notification = new BemVindoNotification();
        $notifiable   = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->greeting)->toBe('Seja bem vindo!');
    });

    test('deve ter as linhas corretas', function (): void {
        $notification = new BemVindoNotification();
        $notifiable   = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->introLines)->toHaveCount(2);
        expect($mail->introLines[0])->toContain('Obrigado');
        expect($mail->introLines[1])->toContain('logo recebera');
    });

    test('deve ter salutation correta', function (): void {
        $notification = new BemVindoNotification();
        $notifiable   = m::mock('stdClass');

        $mail = $notification->toMail($notifiable);

        expect($mail->salutation)->toBe('Atenciosamente');
    });

    test('toArray deve retornar array vazio', function (): void {
        $notification = new BemVindoNotification();
        $notifiable   = m::mock('stdClass');

        expect($notification->toArray($notifiable))->toBe([]);
    });
});
