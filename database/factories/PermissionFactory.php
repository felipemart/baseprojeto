<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'permission' => $this->faker->unique()->slug() . '_' . $this->faker->randomNumber(5),
            'descricao'  => $this->faker->sentence(),
            'role_id'    => Role::factory(),
        ];
    }
}
