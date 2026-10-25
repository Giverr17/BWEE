<?php

namespace Database\Seeders;

use App\Models\RoadmapItem;
use Illuminate\Database\Seeder;

class RoadmapItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Titles and descriptions (EN and FR) are taken verbatim from the BWEE bilingual brief,
     * "FUTURE STRATEGIC ROADMAP" / "FEUILLE DE ROUTE STRATÉGIQUE" sections.
     */
    public function run(): void
    {
        RoadmapItem::truncate();

        $items = [
            [
                'sort_order' => 1,
                'slug' => 'youth-vocational-bootcamps',
                'icon' => '✂️',
                'colour' => '#7C3AED',
                'title' => [
                    'en' => 'Youth & Student Vocational Trades Bootcamps',
                    'fr' => 'Formations professionnelles pratiques pour les jeunes et les étudiants',
                ],
                'description' => [
                    'en' => 'Practical workshops in beauty, esthetics, catering, and creative trades designed to give young women and students hands-on business skills and an entrepreneurial jumpstart.',
                    'fr' => 'Organisation d’ateliers pratiques dans les domaines de la beauté, de l’esthétique, de la restauration et des métiers créatifs afin de permettre aux jeunes femmes et aux étudiants d’acquérir des compétences professionnelles concrètes et de débuter leur parcours entrepreneurial.',
                ],
            ],
            [
                'sort_order' => 2,
                'slug' => 'ai-digital-future-of-work-labs',
                'icon' => '🤖',
                'colour' => '#2563EB',
                'title' => [
                    'en' => 'AI & Digital Future-of-Work Labs',
                    'fr' => 'Laboratoires d’intelligence artificielle et de compétences numériques pour l’avenir du travail',
                ],
                'description' => [
                    'en' => 'Interactive AI and digital tool training specifically tailored for young entrepreneurs, students, and business owners looking to automate and scale operations.',
                    'fr' => 'Mise en place de formations interactives sur l’IA et les outils numériques, spécialement conçues pour les jeunes entrepreneurs, les étudiants et les propriétaires d’entreprises souhaitant automatiser leurs activités et développer leurs entreprises.',
                ],
            ],
            [
                'sort_order' => 3,
                'slug' => 'accessible-youth-business-incubators',
                'icon' => '🏢',
                'colour' => '#DC2626',
                'title' => [
                    'en' => 'Accessible Youth Business Incubators',
                    'fr' => 'Incubateurs d’entreprises accessibles aux jeunes',
                ],
                'description' => [
                    'en' => 'Specialized business hubs built with full accessibility features for young entrepreneurs with disabilities and inclusive spaces for LGBTQ+ youth founders.',
                    'fr' => 'Création de pôles d’accompagnement entrepreneurial dotés de dispositifs d’accessibilité complets pour les jeunes entrepreneurs en situation de handicap, ainsi que d’espaces inclusifs pour les jeunes fondateurs LGBTQ+.',
                ],
            ],
            [
                'sort_order' => 4,
                'slug' => 'grant-navigation-pitch-clinics',
                'icon' => '💰',
                'colour' => '#059669',
                'title' => [
                    'en' => 'Grant Navigation & Investment Pitch Clinics',
                    'fr' => 'Ateliers d’accès aux subventions et de présentation de projets aux investisseurs',
                ],
                'description' => [
                    'en' => 'Organizing hands-on training sessions designed to help business owners and young entrepreneurs to submit funding applications.',
                    'fr' => 'Organisation de formations pratiques destinées à aider les propriétaires d’entreprises et les jeunes entrepreneurs à soumettre des demandes de financements non dilutifs auprès des gouvernements, des entreprises et des programmes de soutien à l’entrepreneuriat jeunesse.',
                ],
            ],
            [
                'sort_order' => 5,
                'slug' => 'national-intergenerational-mentorship-network',
                'icon' => '🤝',
                'colour' => '#D97706',
                'title' => [
                    'en' => 'National Intergenerational Mentorship Network',
                    'fr' => 'Réseau national de mentorat intergénérationnel',
                ],
                'description' => [
                    'en' => 'Pairing established corporate leaders and seasoned business owners with emerging young women and student entrepreneurs across Canada.',
                    'fr' => 'Mise en relation de dirigeantes et dirigeants d’entreprise expérimentés ainsi que de propriétaires d’entreprises établies avec de jeunes femmes et étudiantes entrepreneures émergentes partout au Canada.',
                ],
            ],
            [
                'sort_order' => 6,
                'slug' => 'bilingual-resource-hub',
                'icon' => '📚',
                'colour' => '#0891B2',
                'title' => [
                    'en' => 'Bilingual Resource Hub',
                    'fr' => 'Centre de ressources bilingues',
                ],
                'description' => [
                    'en' => 'Expanding translated toolkits, research briefs, and dual-language community forums for Francophone and Anglophone network members.',
                    'fr' => 'Développement de trousses d’outils traduites, de notes de recherche et de forums communautaires bilingues afin de faciliter l’accès aux ressources pour les membres francophones et anglophones du réseau.',
                ],
            ],
        ];

        foreach ($items as $item) {
            RoadmapItem::create(array_merge(['is_active' => true], $item));
        }
    }
}
