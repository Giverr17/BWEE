<?php

namespace Database\Seeders;

use App\Models\Founder;
use Illuminate\Database\Seeder;

class FounderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Bios use only what the brief states. Fuller bios will come from the founders themselves.
     */
    public function run(): void
    {
        Founder::truncate();

        $bio = [
            'en' => 'Co-founder of the Black Women’s Enterprise Exchange (BWEE), hosted at the Harriet Tubman Institute for Research on Africa and Its Diaspora, York University.',
            'fr' => 'Cofondatrice du Black Women’s Enterprise Exchange (BWEE), accueilli par l’Institut Harriet Tubman pour la recherche sur l’Afrique et sa diaspora à l’Université York.',
        ];

        Founder::create([
            'sort_order' => 1,
            'is_active' => true,
            'name' => 'Dr. Mary Uhunoma Isibor',
            'role' => ['en' => 'Co-Founder', 'fr' => 'Cofondatrice'],
            'bio' => $bio,
            'photo' => 'storage/hero/speaker-slide1.jpg',
            'video_url' => null,
            'audio_url' => null,
            'linkedin_url' => null,
            'twitter_url' => null,
            'email' => 'misibor@yorku.ca', // Brief: co-founder email
        ]);

        Founder::create([
            'sort_order' => 2,
            'is_active' => true,
            'name' => 'Dr. Georgette Arielle Djoufan Fotsing',
            'role' => ['en' => 'Co-Founder', 'fr' => 'Cofondatrice'],
            'bio' => $bio,
            'photo' => 'storage/hero/speaker-slide2.jpg',
            'video_url' => null,
            'audio_url' => null,
            'linkedin_url' => null,
            'twitter_url' => null,
            'email' => 'adjoufan@yorku.ca', // Brief: co-founder email
        ]);
    }
}
