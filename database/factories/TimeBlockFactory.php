<?php

namespace Database\Factories;

use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeBlockFactory extends Factory
{
    protected $model = TimeBlock::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+2 days');
        $end = (clone $start)->modify('+1 hour');
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->word(),
            'start_time' => $start,
            'end_time' => $end,
            'color' => $this->faker->optional()->hexColor(),
        ];
    }
} 