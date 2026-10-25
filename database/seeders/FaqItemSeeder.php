<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqItemSeeder extends Seeder
{
    public function run(): void
    {
        FaqItem::truncate();

        $faqs = [
            [
                'question' => [
                    'en' => 'Who is BWEE for?',
                    'fr' => 'À qui s’adresse BWEE ?',
                ],
                'answer' => [
                    'en' => 'BWEE is for Black women entrepreneurs, small business owners, young women, students, scholars, professionals, emerging youth leaders, newcomers, and immigrant women across Canada. We are explicitly committed to Equity, Diversity, and Inclusion (EDI) and Anti-Racism. Our platform provides tailored structural support for Black women and youth with disabilities, and ensures full inclusion and advocacy for Black LGBTQ+ women and youth navigating the small business landscape.',
                    'fr' => 'BWEE s’adresse aux femmes entrepreneures noires, aux propriétaires de petites entreprises, aux jeunes femmes, aux étudiantes, aux chercheures, aux professionnelles, aux jeunes leaders émergentes, aux nouvelles arrivantes et aux femmes immigrantes partout au Canada. Nous sommes profondément engagés en faveur de l’équité, de la diversité et de l’inclusion (EDI) ainsi que dans la lutte contre le racisme. Notre plateforme offre un soutien structurel sur mesure aux femmes noires et aux jeunes en situation de handicap, et garantit une inclusion totale ainsi qu’un soutien aux femmes et aux jeunes LGBTQ+ noirs qui évoluent dans le monde de la petite entreprise.',
                ],
            ],
            [
                'question' => [
                    'en' => 'How can I get involved?',
                    'fr' => 'Comment puis-je participer ?',
                ],
                'answer' => [
                    'en' => 'You can join as a Member or Youth/Student to access upcoming vocational trades bootcamps, AI skill workshops, grant notices, and bilingual networking events. You can also Partner & Sponsor to support our EDI, anti-racism, youth enterprise, and vocational trades initiatives, or Volunteer & Mentor by sharing your expertise as an executive mentor, AI/tech facilitator, or vocational trainer.',
                    'fr' => 'Vous pouvez devenir membre, jeune membre ou étudiant membre pour accéder à nos prochaines formations professionnelles, à nos ateliers sur l’intelligence artificielle, aux annonces de subventions et aux événements de réseautage bilingues. Vous pouvez aussi devenir partenaire ou commanditaire pour soutenir nos initiatives d’équité, de diversité et d’inclusion, de lutte contre le racisme, d’entrepreneuriat jeunesse et de formation professionnelle, ou devenir bénévole ou mentor en partageant votre expertise en tant que mentor de direction, formateur en IA et technologies ou formateur professionnel.',
                ],
            ],
            [
                'question' => [
                    'en' => 'Is BWEE bilingual?',
                    'fr' => 'BWEE est-il bilingue ?',
                ],
                'answer' => [
                    'en' => 'Yes. BWEE maintains a strategic commitment to bilingual inclusion in English and French to eliminate linguistic isolation. We fully integrate Francophone Black women, youth, and students in Quebec and official language minority communities across Canada into our national business networks.',
                    'fr' => 'Oui. BWEE maintient un engagement stratégique en faveur de l’inclusion bilingue en anglais et en français afin d’éliminer l’exclusion linguistique. Nous intégrons pleinement les femmes noires francophones, les jeunes et les étudiants d’expression française du Québec et des communautés de langue officielle en situation minoritaire partout au Canada à nos réseaux d’affaires nationaux.',
                ],
            ],
            [
                'question' => [
                    'en' => 'What kind of events does BWEE host?',
                    'fr' => 'Quels types d’événements BWEE organise-t-il ?',
                ],
                'answer' => [
                    'en' => 'We host virtual and in-person summits, vocational bootcamps, AI and digital skills workshops, grant navigation clinics, bilingual networking events, research panels, and mentorship forums, all designed to build knowledge, connection, and economic opportunity for Black women, young women, and youth in business.',
                    'fr' => 'Nous organisons des sommets virtuels et en personne, des formations professionnelles intensives, des ateliers sur l’IA et les compétences numériques, des ateliers d’accès aux subventions, des événements de réseautage bilingues, des panels de recherche et des forums de mentorat, tous conçus pour développer les connaissances, les liens et les débouchés économiques des femmes noires, des jeunes femmes et des jeunes en affaires.',
                ],
            ],
            [
                'question' => [
                    'en' => 'How is BWEE funded?',
                    'fr' => 'Comment BWEE est-il financé ?',
                ],
                'answer' => [
                    'en' => 'BWEE operates as a pilot program with support from institutional partners, sponsorships, and grants. We welcome new partners who share our vision for economic equity.',
                    'fr' => 'BWEE fonctionne comme un programme pilote avec le soutien de partenaires institutionnels, de commandites et de subventions. Nous accueillons avec plaisir les nouveaux partenaires qui partagent notre vision de l’équité économique.',
                ],
            ],
        ];

        foreach ($faqs as $i => $f) {
            FaqItem::create([
                'sort_order' => $i + 1,
                'is_active' => true,
                'question' => $f['question'],
                'answer' => $f['answer'],
            ]);
        }
    }
}
