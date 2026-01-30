<?php

declare(strict_types = 1);

test('obfuscarEmail ofusca email corretamente', function (): void {
    $email      = 'john.doe@example.com';
    $obfuscated = obfuscarEmail($email);

    expect($obfuscated)->toContain('@example.com');
    expect($obfuscated)->toContain('*');
    expect($obfuscated)->not->toBe($email);
});

test('obfuscarEmail lida com emails curtos', function (): void {
    $email      = 'a@example.com';
    $obfuscated = obfuscarEmail($email);

    expect($obfuscated)->toContain('@example.com');
});

test('obfuscarEmail retorna string vazia para null', function (): void {
    $result = obfuscarEmail(null);
    expect($result)->toBe('');
});

test('obfuscarEmail retorna string vazia para string vazia', function (): void {
    $result = obfuscarEmail('');
    expect($result)->toBe('');
});

test('obfuscarEmail retorna string vazia para formato de email inválido', function (): void {
    $result = obfuscarEmail('not-an-email');
    expect($result)->toBe('');
});

test('obfuscarEmail lida com emails com múltiplos símbolos @', function (): void {
    $result = obfuscarEmail('user@@example.com');
    expect($result)->toBe('');
});

test('obfuscarEmail ofusca 75 porcento do nome de usuário', function (): void {
    $email      = 'testuser@example.com'; // 8 chars before @
    $obfuscated = obfuscarEmail($email);

    // 75% of 8 = 6, so should have 6 asterisks
    $asteriskCount = substr_count($obfuscated, '*');
    expect($asteriskCount)->toBe(6);
});

test('obfuscarEmail preserva o domínio', function (): void {
    $email      = 'john@example.com';
    $obfuscated = obfuscarEmail($email);

    expect($obfuscated)->toEndWith('@example.com');
});
