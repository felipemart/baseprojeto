<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['permission' => 'user.create', 'role_id' => 3, 'descricao' => 'Cadastro de Usuários']);
        Permission::create(['permission' => 'user.edit', 'role_id' => 3, 'descricao' => 'Edição de Usuários']);
        Permission::create(['permission' => 'user.delete', 'role_id' => 3, 'descricao' => 'Exclusão de Usuários']);
        Permission::create(['permission' => 'user.list', 'role_id' => 3, 'descricao' => 'Listagem de Usuários']);
        Permission::create(['permission' => 'user.permission', 'role_id' => 3, 'descricao' => 'Permisão de Usuários']);
    }
}
