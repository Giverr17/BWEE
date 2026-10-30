<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventRegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+1 416 555 0'.$this->faker->numberBetween(100, 199),
            'country_of_residence' => 'CA',
            'nationality' => 'NG',
            'profession' => $this->faker->jobTitle(),
            'organization' => $this->faker->company(),
            'expectations' => $this->faker->sentence(10),
            'consent_at' => now(),
            'locale' => 'en',
            'newsletter_consent' => false,
        ];
    }
}
