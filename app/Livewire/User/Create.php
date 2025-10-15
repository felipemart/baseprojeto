<?php

declare(strict_types = 1);

namespace App\Livewire\User;

use App\Models\Role;
use App\Models\User;
use App\Notifications\BemVindoNotification;
use App\Notifications\EmailCriacaoSenha;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Mary\Traits\Toast;

class Create extends Component
{
    use Toast;

    public ?User $user = null;

    public Collection $roles;


    public ?int $roleSelect = null;


    public ?int $id = null;

    public int $perPage = 10;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public ?string $search = null;

    public array $setPermissions = [];

    public string $name = '';

    public string $email = '';

    public int $step = 1;

    public bool $saveOnly = false;

    public function mount(): void
    {
        $role = auth()->user()->role_id;

        $this->roles = Role::query()
            ->where('id', '>=', $role)
            ->orderBy('name')
            ->get();
    }

    public function render(): \Illuminate\Contracts\View\View | \Illuminate\Contracts\View\Factory
    {
        return view('livewire.user.create');
    }

    protected function rules(): array
    {
        return [
            'name'       => 'required',
            'email'      => 'required|email|unique:users,email,' . $this->id,
            'roleSelect' => 'required |exists:roles,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'roleSelect.required' => 'O campo Nivel de acesso é obrigatório.',
            'required'            => 'O campo :attribute é obrigatório.',
            'email'               => 'O campo :attribute deve ser um e-mail válido.',
            'unique'              => 'O campo :attribute já existe.',

        ];
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'permission', 'label' => 'Permissão'],
        ];
    }

    

    public function save(): bool
    {
        $this->validate();
        $this->user = User::create([
            'name'       => $this->name,
            'email'      => $this->email,
            'password'   => bcrypt(str()->random(10)),
            'role_id'    => $this->roleSelect,

        ]);

        $this->user->notify(new BemVindoNotification());
        $token = Password::createToken($this->user);
        $this->user->notify(new EmailCriacaoSenha($token));

        if ($this->user->name) {
            $this->success(
                'Usuário criado com sucesso!',
                null,
                'toast-top toast-end',
                'o-information-circle',
                'alert-info',
                3000
            );

            return true;
        }
        $this->error(
            'Erro ao criar o usuário!',
            null,
            'toast-top toast-end',
            'o-exclamation-triangle',
            'alert-info',
            3000
        );

        return false;
    }
}
