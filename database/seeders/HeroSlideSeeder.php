<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Slide text uses the wording of the BWEE bilingual brief (title block and About section).
     */
    public function run(): void
    {
        HeroSlide::truncate();

        // Slide 1: Brand / Logo Showcase (Center Layout)
        HeroSlide::create([
            'sort_order' => 1,
            'is_active' => true,
            'layout' => 'center',
            'eyebrow' => null,
            'heading' => [
                'en' => 'Building Capital, Strategy, and Transnational Economic Power',
                'fr' => 'Construction du capital, de la stratégie et de la puissance économique transnationale',
            ],
            'sub_heading' => [
                'en' => 'A Research-Informed, Dual-Language Initiative',
                'fr' => 'Une initiative bilingue fondée sur la recherche',
            ],
            'bio' => [
                'en' => 'Hosted at the Harriet Tubman Institute, York University.',
                'fr' => 'Accueillie par l’Institut Harriet Tubman de l’Université York.',
            ],
            'cta_primary_text' => [
                'en' => 'Partner & Sponsor',
                'fr' => 'Devenir partenaire ou commanditaire',
            ],
            'cta_primary_url' => '#involved',
            'cta_secondary_text' => [
                'en' => 'See Events',
                'fr' => 'Voir les événements',
            ],
            'cta_secondary_url' => '#events',
            'brand_logo' => 'images/bwee-symbol-logo.png',
            'bg_color' => '#3a195e',
            'video_url' => null,
            'audio_url' => null,
        ]);

        // Slide 2: Single Split Layout (50/50 Co-Founders)
        HeroSlide::create([
            'sort_order' => 2,
            'is_active' => true,
            'layout' => 'split',
            'eyebrow' => [
                'en' => "BLACK WOMEN'S ENTERPRISE EXCHANGE",
                'fr' => 'ÉCHANGE ENTREPRENEURIAL DES FEMMES NOIRES (BWEE)', // proposed translation, to be approved by the founders
            ],
            'heading' => [
                'en' => 'The Co-founders',
                'fr' => 'Les cofondatrices',
            ],
            'sub_heading' => [
                'en' => 'Dr. Mary Uhunoma Isibor & Dr. Georgette Arielle Djoufan Fotsing',
                'fr' => 'Dre Mary Uhunoma Isibor et Dre Georgette Arielle Djoufan Fotsing',
            ],
            'bio' => [
                'en' => 'Grounded in doctoral and postdoctoral research on Black women entrepreneurs in Toronto and across international diaspora contexts.',
                'fr' => 'S’appuyant sur des recherches doctorales et postdoctorales menées auprès de femmes entrepreneurs noires à Toronto et dans divers contextes de la diaspora internationale.',
            ],
            'cta_primary_text' => [
                'en' => 'Meet the Co-founders',
                'fr' => 'Rencontrer les cofondatrices',
            ],
            'cta_primary_url' => '#founders',
            'cta_secondary_text' => [
                'en' => 'See Events',
                'fr' => 'Voir les événements',
            ],
            'cta_secondary_url' => '#events',
            'bg_image_left' => 'storage/hero/speaker-slide1.jpg',
            'bg_image_right' => 'storage/hero/speaker-slide2.jpg',
            'photo_path_left' => 'storage/hero/speaker-slide1.jpg',
            'photo_path_right' => 'storage/hero/speaker-slide2.jpg',
            'video_url' => null,
            'audio_url' => null,
        ]);
    }
}
