<?php

use App\Models\User;
use App\Notifications\BemVindoNotification;
use Illuminate\Support\Facades\Notification;

test('bem vindo notification can be created', function () {
    $notification = new BemVindoNotification();
    
    expect($notification)->toBeInstanceOf(BemVindoNotification::class);
});

test('bem vindo notification uses mail channel', function () {
    $user = User::factory()->create();
    $notification = new BemVindoNotification();
    
    $channels = $notification->via($user);
    
    expect($channels)->toContain('mail');
});

test('bem vindo notification has correct mail structure', function () {
    $user = User::factory()->create();
    $notification = new BemVindoNotification();
    
    $mail = $notification->toMail($user);
    
    expect($mail->subject)->toBe('Email de boas vindas');
});

test('bem vindo notification has greeting', function () {
    $user = User::factory()->create();
    $notification = new BemVindoNotification();
    
    $mail = $notification->toMail($user);
    
    expect($mail->greeting)->toBe('Seja bem vindo!');
});

test('bem vindo notification to array returns empty', function () {
    $user = User::factory()->create();
    $notification = new BemVindoNotification();
    
    $array = $notification->toArray($user);
    
    expect($array)->toBeArray();
    expect($array)->toBeEmpty();
});

test('user can receive bem vindo notification', function () {
    Notification::fake();
    
    $user = User::factory()->create();
    
    $user->notify(new BemVindoNotification());
    
    Notification::assertSentTo($user, BemVindoNotification::class);
});

