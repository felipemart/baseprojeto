<?php

test('obfuscarEmail obfuscates email correctly', function () {
    $email = 'john.doe@example.com';
    $obfuscated = obfuscarEmail($email);
    
    expect($obfuscated)->toContain('@example.com');
    expect($obfuscated)->toContain('*');
    expect($obfuscated)->not->toBe($email);
});

test('obfuscarEmail handles short emails', function () {
    $email = 'a@example.com';
    $obfuscated = obfuscarEmail($email);
    
    expect($obfuscated)->toContain('@example.com');
});

test('obfuscarEmail returns empty string for null', function () {
    $result = obfuscarEmail(null);
    expect($result)->toBe('');
});

test('obfuscarEmail returns empty string for empty string', function () {
    $result = obfuscarEmail('');
    expect($result)->toBe('');
});

test('obfuscarEmail returns empty string for invalid email format', function () {
    $result = obfuscarEmail('not-an-email');
    expect($result)->toBe('');
});

test('obfuscarEmail handles emails with multiple @ symbols', function () {
    $result = obfuscarEmail('user@@example.com');
    expect($result)->toBe('');
});

test('obfuscarEmail obfuscates 75 percent of username', function () {
    $email = 'testuser@example.com'; // 8 chars before @
    $obfuscated = obfuscarEmail($email);
    
    // 75% of 8 = 6, so should have 6 asterisks
    $asteriskCount = substr_count($obfuscated, '*');
    expect($asteriskCount)->toBe(6);
});

test('obfuscarEmail preserves domain', function () {
    $email = 'john@example.com';
    $obfuscated = obfuscarEmail($email);
    
    expect($obfuscated)->toEndWith('@example.com');
});

