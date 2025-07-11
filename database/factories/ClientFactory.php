<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->e164PhoneNumber(),
            'birth_date' => $this->faker->date(),
            'cpf_nif' => $this->faker->cpf(false),
            'emergency_contact' => $this->faker->name() . ' - ' . $this->faker->phoneNumber(),
            'case_summary' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
    }
}
