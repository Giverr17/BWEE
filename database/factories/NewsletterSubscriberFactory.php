<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterSubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'locale' => 'en',
            'source' => 'web',
            'subscribed_at' => now(),
            'consented_at' => now(),
            'unsubscribed_at' => null,
        ];
    }
}
