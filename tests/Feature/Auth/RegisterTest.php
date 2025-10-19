<?php

declare(strict_types = 1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = createAdminWithSession();
});

test('página de registro requer autenticação', function (): void {
    $this->get(route('auth.register'))
        ->assertRedirect(route('login'));
});

test('admin pode acessar página de registro', function (): void {
    $this->actingAs($this->admin)
        ->get(route('auth.register'))
        ->assertOk();
});

test('componente de registro pode ser montado', function (): void {
    $this->actingAs($this->admin);

    $component = Livewire::test(Register::class);

    expect($component)->not->toBeNull();
});

test('registro requer nome', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', '')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['name' => 'required']);
});

test('registro requer email', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', '')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'required']);
});

test('registro requer formato de email válido', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'not-an-email')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'email']);
});

test('registro requer senha', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('email_confirmation', 'test@example.com')
        ->set('password', '')
        ->call('registrarUsuario')
        ->assertHasErrors(['password' => 'required']);
});

test('registro requer confirmação de email', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('email_confirmation', 'different@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasErrors(['email' => 'confirmed']);
});

test('registro requer email único', function (): void {
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

test('registro respeita tamanho máximo do nome', function (): void {
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

test('registro respeita tamanho máximo do email', function (): void {
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

test('registro cria usuário com sucesso e envia notificação', function (): void {
    $this->actingAs($this->admin);
    
    Notification::fake();
    
    $userCount = User::count();

    Livewire::test(Register::class)
        ->set('name', 'New User')
        ->set('email', 'newuser@example.com')
        ->set('email_confirmation', 'newuser@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(User::count())->toBe($userCount + 1);
    
    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New User');
    expect($user->email)->toBe('newuser@example.com');
    
    Notification::assertSentTo(
        $user,
        \App\Notifications\BemVindoNotification::class
    );
});

test('registro faz hash da senha corretamente', function (): void {
    $this->actingAs($this->admin);
    
    Notification::fake();

    Livewire::test(Register::class)
        ->set('name', 'Test User Password')
        ->set('email', 'testpassword@example.com')
        ->set('email_confirmation', 'testpassword@example.com')
        ->set('password', 'password123')
        ->call('registrarUsuario');

    $user = User::where('email', 'testpassword@example.com')->first();
    expect($user->password)->not->toBeNull();
    expect(strlen($user->password))->toBeGreaterThan(20); // Senha com hash
});
