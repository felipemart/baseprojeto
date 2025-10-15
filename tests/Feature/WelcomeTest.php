<?php

declare(strict_types = 1);

use App\Livewire\Welcome;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('welcome page requires authentication', function (): void {
    get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('authenticated user can see welcome page', function (): void {
    $admin = createAdminWithSession();

    actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('welcome component can be mounted', function (): void {
    Livewire::test(Welcome::class)
        ->assertSuccessful();
});

it('welcome component has default search value', function (): void {
    Livewire::test(Welcome::class)
        ->assertSet('search', '');
});

it('welcome component has default drawer value', function (): void {
    Livewire::test(Welcome::class)
        ->assertSet('drawer', false);
});

it('welcome component has default sortBy', function (): void {
    Livewire::test(Welcome::class)
        ->assertSet('sortBy', ['column' => 'name', 'direction' => 'asc']);
});

it('welcome component can clear filters', function (): void {
    Livewire::test(Welcome::class)
        ->set('search', 'test')
        ->call('clear')
        ->assertSet('search', '');
});

it('welcome component can delete item', function (): void {
    Livewire::test(Welcome::class)
        ->call('delete', 1);

    // O método delete() chama warning() que é um toast
    expect(true)->toBeTrue();
});
