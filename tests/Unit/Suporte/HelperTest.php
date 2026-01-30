<?php

declare(strict_types = 1);

require_once __DIR__ . '/../../../app/Suporte/Helper.php';

describe('obfuscarEmail', function (): void {
    test('deve obfuscar email corretamente', function (): void {
        $email  = 'teste@example.com';
        $result = obfuscarEmail($email);

        expect($result)
            ->toContain('@example.com')
            ->toContain('*');
    });

    test('deve obfuscar 75% do nome do email', function (): void {
        $email  = 'testando@example.com';
        $result = obfuscarEmail($email);

        // testando tem 8 caracteres, 75% = 6 asteriscos
        $asteriscos = substr_count($result, '*');
        expect($asteriscos)->toBe(6);
        expect($result)->toStartWith('te');
    });

    test('deve retornar string vazia para email null', function (): void {
        $result = obfuscarEmail(null);
        expect($result)->toBe('');
    });

    test('deve retornar string vazia para email vazio', function (): void {
        $result = obfuscarEmail('');
        expect($result)->toBe('');
    });

    test('deve retornar string vazia para string zero', function (): void {
        $result = obfuscarEmail('0');
        expect($result)->toBe('');
    });

    test('deve retornar string vazia para email sem @', function (): void {
        $result = obfuscarEmail('emailinvalido');
        expect($result)->toBe('');
    });

    test('deve retornar string vazia para email com múltiplos @', function (): void {
        $result = obfuscarEmail('test@test@example.com');
        expect($result)->toBe('');
    });

    test('deve obfuscar email curto corretamente', function (): void {
        $email  = 'ab@example.com';
        $result = obfuscarEmail($email);

        expect($result)
            ->toContain('@example.com')
            ->toStartWith('a');
    });

    test('deve obfuscar email com um caractere antes do @', function (): void {
        $email  = 'a@example.com';
        $result = obfuscarEmail($email);

        expect($result)->toBe('a@example.com');
    });
});
