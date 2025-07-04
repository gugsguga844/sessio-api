<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

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