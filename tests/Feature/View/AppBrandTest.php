<?php

declare(strict_types = 1);

use App\View\Components\AppBrand;

test('componente app brand pode ser criado', function (): void {
    $component = new AppBrand();

    expect($component)->toBeInstanceOf(AppBrand::class);
});

test('componente app brand pode ser renderizado', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toBeString();
    expect($view)->toContain('app');
});

test('componente app brand contém link', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toContain('<a href="/"');
    expect($view)->toContain('wire:navigate');
});

test('componente app brand contém ícone', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toContain('o-cube');
    expect($view)->toContain('s-cube');
});

test('componente app brand contém estados colapsados', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toContain('hidden-when-collapsed');
    expect($view)->toContain('display-when-collapsed');
});
