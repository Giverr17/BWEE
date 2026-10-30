<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->sentence(4),
                'fr' => $this->faker->sentence(4),
            ],
            'event_date' => Carbon::tomorrow()->format('Y-m-d'),
            'registration_open' => true,
        ];
    }
}

