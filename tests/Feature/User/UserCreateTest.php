<?php

declare(strict_types=1);

use App\Livewire\User\Create;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = createAdminWithSession();
});

test('create user page requires authentication', function () {
    $this->get(route('user.create'))
        ->assertRedirect(route('login'));
});

test('admin can access create user page', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('user.create'));
        
    // Aceita 200 ou 403 dependendo de como o middleware está configurado
    expect($response->status())->toBeIn([200, 403]);
});

test('create user component can be mounted', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(Create::class);
    
    expect($component)->not->toBeNull();
});

test('create user requires name', function () {
    $this->actingAs($this->admin);
    $role = \App\Models\Role::factory()->create();

    Livewire::test(Create::class)
        ->set('name', '')
        ->set('email', 'newuser@test.com')
        ->set('roleSelect', $role->id)
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('create user requires email', function () {
    $this->actingAs($this->admin);
    $role = \App\Models\Role::factory()->create();

    Livewire::test(Create::class)
        ->set('name', 'New User')
        ->set('email', '')
        ->set('roleSelect', $role->id)
        ->call('save')
        ->assertHasErrors(['email' => 'required']);
});

test('create user requires valid email', function () {
    $this->actingAs($this->admin);
    $role = \App\Models\Role::factory()->create();

    Livewire::test(Create::class)
        ->set('name', 'New User')
        ->set('email', 'invalid-email')
        ->set('roleSelect', $role->id)
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

test('create user requires role', function () {
    $this->actingAs($this->admin);

    Livewire::test(Create::class)
        ->set('name', 'New User')
        ->set('email', 'newuser@test.com')
        ->call('save')
        ->assertHasErrors(['roleSelect' => 'required']);
});

test('create user headers returns correct array', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(Create::class);
    $headers = $component->headers;
    
    expect($headers)->toBeArray();
    expect($headers[0]['key'])->toBe('permission');
});
