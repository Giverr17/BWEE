<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

// use Illuminate\Support\Facades\DB;


class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * The launch event is the only event in the BWEE bilingual brief. EN and FR text is verbatim from it.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Event::truncate();
        Schema::enableForeignKeyConstraints();

        // Inaugural launch: all values from the brief.
        Event::create([
            'sort_order' => 1,
            'is_active' => true,
            'is_upcoming' => false,
            'title' => [
                'en' => 'BWEE Global Virtual Launch',
                'fr' => 'Lancement mondial en ligne de BWEE',
            ],
            'tag' => [
                'en' => 'Launch',
                'fr' => 'Lancement',
            ],
            'description' => [
                'en' => 'On August 25, 2026, BWEE officially introduced its vision during its global virtual launch event themed "From Survival to Strategy: Building, Scaling, and Sustaining Black Women-Owned Businesses". Hosted in partnership with the Harriet Tubman Institute at York University and the School of Women in Leadership & Business, the event convened 172 registered participants representing 62 people established in Canada across 8 provinces and international delegates across 13 confirmed countries.',
                'fr' => 'Le 25 août 2026, BWEE a officiellement présenté sa vision lors de son événement inaugural mondial de lancement en ligne, organisé sous le thème : « De la survie à la stratégie : construire, développer et pérenniser les entreprises appartenant à des femmes noires ». Organisé en partenariat avec l’Institut Harriet Tubman de l’Université York et la School of Women in Leadership & Business, cet événement a réuni 172 participants inscrits, dont 62 personnes établies au Canada provenant de 8 provinces et des délégués internationaux issus de 13 pays confirmés.',
            ],
            // Speaker titles are stored as EN/FR pairs inside the JSON (read them with the current locale in the view).
            'speakers' => [
                ['name' => 'Dr. Salewa Olawoye', 'title' => ['en' => 'Director, Harriet Tubman Institute', 'fr' => 'Directrice de l’Institut Harriet Tubman']],
                ['name' => 'Prof. Peju Layiwola', 'title' => ['en' => 'Founder, WYAF', 'fr' => 'Fondatrice de WYAF']],
                ['name' => 'Dr. Nikki Clarke', 'title' => ['en' => 'Founder, The Nikki Clarke Network', 'fr' => 'Fondatrice du Nikki Clarke Network']],
            ],
            'date_label' => [
                'en' => 'August 25, 2026',
                'fr' => '25 août 2026',
            ],
            'event_date' => '2026-08-25',
            'location' => [
                'en' => 'Virtual',
                'fr' => 'En ligne',
            ],
            'thumbnail' => null,
            'video_url' => null,
            'audio_url' => null,
            'cta_text' => [
                'en' => 'Learn More',
                'fr' => 'En savoir plus',
            ],
            'cta_url' => '#events',
        ]);
    }
}
