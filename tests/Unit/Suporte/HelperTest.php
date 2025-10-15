<?php

declare(strict_types = 1);

require_once __DIR__ . '/../../../app/suporte/Helper.php';

describe('obfuscarEmail', function () {
    it('deve obfuscar email corretamente', function () {
        $email = 'teste@example.com';
        $result = obfuscarEmail($email);

        expect($result)
            ->toContain('@example.com')
            ->toContain('*');
    });

    it('deve obfuscar 75% do nome do email', function () {
        $email = 'testando@example.com';
        $result = obfuscarEmail($email);

        // testando tem 8 caracteres, 75% = 6 asteriscos
        $asteriscos = substr_count($result, '*');
        expect($asteriscos)->toBe(6);
        expect($result)->toStartWith('te');
    });

    it('deve retornar string vazia para email null', function () {
        $result = obfuscarEmail(null);
        expect($result)->toBe('');
    });

    it('deve retornar string vazia para email vazio', function () {
        $result = obfuscarEmail('');
        expect($result)->toBe('');
    });

    it('deve retornar string vazia para string zero', function () {
        $result = obfuscarEmail('0');
        expect($result)->toBe('');
    });

    it('deve retornar string vazia para email sem @', function () {
        $result = obfuscarEmail('emailinvalido');
        expect($result)->toBe('');
    });

    it('deve retornar string vazia para email com múltiplos @', function () {
        $result = obfuscarEmail('test@test@example.com');
        expect($result)->toBe('');
    });

    it('deve obfuscar email curto corretamente', function () {
        $email = 'ab@example.com';
        $result = obfuscarEmail($email);

        expect($result)
            ->toContain('@example.com')
            ->toStartWith('a');
    });

    it('deve obfuscar email com um caractere antes do @', function () {
        $email = 'a@example.com';
        $result = obfuscarEmail($email);

        expect($result)->toBe('a@example.com');
    });
});

