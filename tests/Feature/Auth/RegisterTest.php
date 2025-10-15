<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = createAdminWithSession();
});

test('register page requires authentication', function () {
    $this->get(route('auth.register'))
        ->assertRedirect(route('login'));
});

test('admin can access register page', function () {
    $this->actingAs($this->admin)
        ->get(route('auth.register'))
        ->assertOk();
});

test('register component can be mounted', function () {
    $this->actingAs($this->admin);

    $component = Livewire::test(Register::class);
    
    expect($component)->not->toBeNull();
});

test('register requires name', function () {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', '')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['name' => 'required']);
});

test('register requires email', function () {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', '')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'required']);
});

test('register requires valid email format', function () {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'not-an-email')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'email']);
});

test('register requires password', function () {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('email_confirmation', 'test@example.com')
        ->set('password', '')
        ->call('registrarUsuario')
        ->assertHasErrors(['password' => 'required']);
});

test('register requires email confirmation', function () {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('email_confirmation', 'different@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'confirmed']);
});

test('register requires unique email', function () {
    $this->actingAs($this->admin);
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'existing@example.com')
        ->set('email_confirmation', 'existing@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'unique']);
});


test('register name respects max length', function () {
    $this->actingAs($this->admin);
    $longName = str_repeat('a', 256);

    Livewire::test(Register::class)
        ->set('name', $longName)
        ->set('email', 'test@example.com')
        ->set('email_confirmation', 'test@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['name' => 'max']);
});

test('register email respects max length', function () {
    $this->actingAs($this->admin);
    $longEmail = str_repeat('a', 250) . '@test.com';

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', $longEmail)
        ->set('email_confirmation', $longEmail)
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'max']);
});
