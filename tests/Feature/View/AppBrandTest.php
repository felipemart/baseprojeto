<?php

declare(strict_types = 1);

use App\View\Components\AppBrand;

test('app brand component can be created', function (): void {
    $component = new AppBrand();

    expect($component)->toBeInstanceOf(AppBrand::class);
});

test('app brand component can render', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toBeString();
    expect($view)->toContain('app');
});

test('app brand component contains link', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toContain('<a href="/"');
    expect($view)->toContain('wire:navigate');
});

test('app brand component contains icon', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toContain('o-cube');
    expect($view)->toContain('s-cube');
});

test('app brand component contains collapsed states', function (): void {
    $component = new AppBrand();

    $view = $component->render();

    expect($view)->toContain('hidden-when-collapsed');
    expect($view)->toContain('display-when-collapsed');
});
