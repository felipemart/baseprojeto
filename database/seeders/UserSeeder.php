<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->withRoles('Admin')
            ->withPermissions('usuario.list')
            ->withPermissions('usuario.permission')
            ->create([
                'name'     => 'Admin',
                'email'    => 'admin@localhost.com',
                'password' => bcrypt('123'),
            ]);
    }
}
