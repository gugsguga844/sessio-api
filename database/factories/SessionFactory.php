<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    protected $model = \App\Models\Session::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+2 days');
        $end = (clone $start)->modify('+1 hour');
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'title' => $this->faker->sentence(3),
            'start_time' => $start,
            'end_time' => $end,
            'notes' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(['presencial', 'online']),
            'payment_status' => $this->faker->randomElement(['pago', 'pendente']),
        ];
    }
} 