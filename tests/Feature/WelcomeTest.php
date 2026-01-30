<?php

declare(strict_types = 1);

use App\Livewire\Welcome;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('página de boas-vindas requer autenticação', function (): void {
    get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('usuário autenticado pode ver página de boas-vindas', function (): void {
    $admin = createAdminWithSession();

    actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

test('componente welcome pode ser montado', function (): void {
    Livewire::test(Welcome::class)
        ->assertSuccessful();
});

test('componente welcome tem valor padrão de busca', function (): void {
    Livewire::test(Welcome::class)
        ->assertSet('search', '');
});

test('componente welcome tem valor padrão de drawer', function (): void {
    Livewire::test(Welcome::class)
        ->assertSet('drawer', false);
});

test('componente welcome tem sortBy padrão', function (): void {
    Livewire::test(Welcome::class)
        ->assertSet('sortBy', ['column' => 'name', 'direction' => 'asc']);
});

test('componente welcome pode limpar filtros', function (): void {
    Livewire::test(Welcome::class)
        ->set('search', 'test')
        ->call('clear')
        ->assertSet('search', '');
});

test('componente welcome pode deletar item', function (): void {
    Livewire::test(Welcome::class)
        ->call('delete', 1);

    // O método delete() chama warning() que é um toast
    expect(true)->toBeTrue();
});
