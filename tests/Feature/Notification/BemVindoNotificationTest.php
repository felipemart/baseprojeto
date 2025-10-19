<?php

declare(strict_types = 1);

use App\Models\User;
use App\Notifications\BemVindoNotification;
use Illuminate\Support\Facades\Notification;

test('notificação de boas-vindas pode ser criada', function (): void {
    $notification = new BemVindoNotification();

    expect($notification)->toBeInstanceOf(BemVindoNotification::class);
});

test('notificação de boas-vindas usa canal de email', function (): void {
    $user         = User::factory()->create();
    $notification = new BemVindoNotification();

    $channels = $notification->via($user);

    expect($channels)->toContain('mail');
});

test('notificação de boas-vindas tem estrutura de email correta', function (): void {
    $user         = User::factory()->create();
    $notification = new BemVindoNotification();

    $mail = $notification->toMail($user);

    expect($mail->subject)->toBe('Email de boas vindas');
});

test('notificação de boas-vindas tem saudação', function (): void {
    $user         = User::factory()->create();
    $notification = new BemVindoNotification();

    $mail = $notification->toMail($user);

    expect($mail->greeting)->toBe('Seja bem vindo!');
});

test('notificação de boas-vindas toArray retorna vazio', function (): void {
    $user         = User::factory()->create();
    $notification = new BemVindoNotification();

    $array = $notification->toArray($user);

    expect($array)->toBeArray();
    expect($array)->toBeEmpty();
});

test('usuário pode receber notificação de boas-vindas', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    $user->notify(new BemVindoNotification());

    Notification::assertSentTo($user, BemVindoNotification::class);
});
