<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        Program::truncate();

        $pillars = [
            [
                'sort_order' => 1,
                'slug' => 'vocational-trades',
                'icon' => '✂️',
                'colour' => '#7C3AED',
                'title' => ['en' => 'Practical Vocational Trades & Youth Enterprise Pathways', 'fr' => 'Formation professionnelle pratique et parcours entrepreneuriaux pour les jeunes'],
                'description' => [
                    'en' => "Hands-on vocational skills acquisition, including hairstyling, makeup artistry, nail technology, catering, culinary arts, and service trades.\nYouth & Student Skill Acceleration: Tailored vocational bootcamps for young women and students to introduce enterprise concepts early, build practical craft mastery, and foster an independent entrepreneurial mindset before or alongside post-secondary education.\nTargeted workforce integration strategies derived from Toronto postdoctoral research to help newcomer women and young entrepreneurs establish licensed, high-revenue small businesses.",
                    'fr' => "Acquisition de compétences professionnelles pratiques, notamment en coiffure, maquillage, soins des ongles, services traiteurs, arts culinaires et autres métiers de services.\nAccélération des compétences des jeunes et des étudiants : organisation de formations intensives adaptées aux jeunes femmes et aux étudiants afin de les initier tôt aux concepts entrepreneuriaux, de développer leur maîtrise de compétences pratiques et de favoriser un esprit d’entreprise autonome avant ou pendant leurs études postsecondaires.\nMise en œuvre de stratégies ciblées d’intégration au marché du travail, inspirées de recherches postdoctorales menées à Toronto, afin d’aider les nouvelles immigrantes et les jeunes entrepreneurs à créer des petites entreprises légalement établies et générant des revenus élevés.",
                ],
            ],
            [
                'sort_order' => 2,
                'slug' => 'ai-technology-digital',
                'icon' => '🤖',
                'colour' => '#2563EB',
                'title' => ['en' => 'AI, Technology & Digital Enterprise Readiness', 'fr' => 'Intelligence artificielle, technologie et préparation à l’entrepreneuriat numérique'],
                'description' => [
                    'en' => "Practical training in Artificial Intelligence (AI) tools, automation, digital marketing, e-commerce, and software for small business efficiency.\nNext-Gen Digital Skills: Equipping young women, students, and youth with AI skills and digital literacy to launch tech-enabled businesses, streamline operations, create content, and remain globally competitive in modern digital markets.",
                    'fr' => "Formation pratique à l’utilisation des outils d’intelligence artificielle (IA), de l’automatisation, du marketing numérique, du commerce électronique et des logiciels permettant d’améliorer l’efficacité des petites entreprises.\nCompétences numériques de nouvelle génération : équiper les jeunes femmes, étudiants et jeunes à acquérir des compétences en IA et en alphabétisation numérique afin de créer des entreprises utilisant les technologies, d’optimiser leurs activités, de produire du contenu et de rester compétitifs sur les marchés numériques modernes.",
                ],
            ],
            [
                'sort_order' => 3,
                'slug' => 'edi-anti-racism',
                'icon' => '✊',
                'colour' => '#DC2626',
                'title' => ['en' => 'EDI, Anti-Racism & Intersectional Equity', 'fr' => 'Équité, diversité, inclusion (EDI), lutte contre le racisme et équité intersectionnelle'],
                'description' => [
                    'en' => "Embedded structural advocacy and anti-racist frameworks across all programs.\nTailored accessibility accommodations and targeted business incubation for Black women and youth with disabilities.\nSafe, inclusive, and affirmative spaces for Black LGBTQ+ small business owners, young entrepreneurs, and youth founders.",
                    'fr' => "Intégration systématique de mesures de défense des droits et de cadres antiracistes dans l’ensemble des programmes.\nMise en place de mesures d’accessibilité adaptées et de programmes ciblés d’incubation d’entreprises pour les femmes noires et les jeunes en situation de handicap.\nCréation d’espaces sécuritaires, inclusifs et valorisants pour les propriétaires de petites entreprises, les jeunes entrepreneurs et les jeunes entrepreneurs noirs LGBTQ+.",
                ],
            ],
            [
                'sort_order' => 4,
                'slug' => 'capital-access-grants',
                'icon' => '💰',
                'colour' => '#059669',
                'title' => ['en' => 'Capital Access, Grants & Financial Literacy', 'fr' => 'Accès au capital, subventions et alphabétisation financière'],
                'description' => [
                    'en' => "Expert guidance on navigating federal, provincial, and foundation grants.\nTraining in proposal writing, pitch presentation, investor readiness, and youth entrepreneurship funding calls.\nCommunity-backed alternative financing models and financial literacy to end over-reliance on personal debt.",
                    'fr' => "Accompagnement spécialisé dans la recherche et l’obtention de subventions fédérales, provinciales et subventions des fondations.\nFormation à la rédaction de propositions de financement, la présentation de projets devant des investisseurs, la préparation à l’investissement et les appels à financement destinés aux jeunes entrepreneurs.\nDéveloppement de modèles alternatifs de financement communautaire et de programmes d’alphabétisation financière afin de réduire la dépendance excessive à l’endettement personnel.",
                ],
            ],
            [
                'sort_order' => 5,
                'slug' => 'enterprise-scaling',
                'icon' => '📈',
                'colour' => '#D97706',
                'title' => ['en' => 'Strategic Enterprise Scaling & Operations', 'fr' => 'Croissance stratégique des entreprises et gestion opérationnelle'],
                'description' => [
                    'en' => "Transitioning micro-enterprises past the critical 5-year survival threshold.\nImplementation of standard operating procedures, team delegation, digital systems, and strategic management to build scalable assets.",
                    'fr' => "Accompagnement des microentreprises afin de leur permettre de dépasser le seuil critique des cinq premières années d’existence.\nMise en place de procédures opérationnelles normalisées, mécanismes de délégation des responsabilités, systèmes numériques et pratiques de gestion stratégique pour transformer les entreprises en structures capables de croître durablement.",
                ],
            ],
            [
                'sort_order' => 6,
                'slug' => 'dual-language',
                'icon' => '🇫🇷',
                'colour' => '#0891B2',
                'title' => ['en' => 'Dual-Language Accessibility (English–French)', 'fr' => 'Accessibilité bilingue (anglais-français)'],
                'description' => [
                    'en' => "Strategic commitment to bilingual inclusion to eliminate linguistic isolation.\nFull integration of French-speaking Black women, French-speaking youth, and students in all Canadian provinces, by considering linguistic minority communities across Canada and within international business networks.",
                    'fr' => "Engagement stratégique en faveur de l’inclusion bilingue afin d’éliminer l’exclusion linguistique.\nIntégration complète des femmes noires francophones, des jeunes et des étudiants d’expression française dans toutes les provinces canadiennes en considérant les communautés en situation de minoritaire linguistique au Canada et dans les réseaux internationaux d’affaires.",
                ],
            ],
            [
                'sort_order' => 7,
                'slug' => 'research-policy-diaspora',
                'icon' => '🌍',
                'colour' => '#6D28D9',
                'title' => ['en' => 'Research-Backed Policy Advocacy & Diaspora Connections', 'fr' => 'Plaidoyer politique fondé sur la recherche et connexions avec la diaspora'],
                'description' => [
                    'en' => "Translating academic research into actionable policy recommendations for Canadian financial institutions, educational bodies, and government agencies.\nLeveraging global diaspora networks across Africa, Europe, the Caribbean, and the Americas for international trade, youth exchange, market expansion, and cross-border mentorship.",
                    'fr' => "Transcrire les résultats de la recherche universitaire en recommandations concrètes de politiques publiques destinées aux institutions financières canadiennes, aux établissements d’enseignement et aux organismes gouvernementaux.\nMobilisation des réseaux mondiaux de la diaspora en Afrique, en Europe, dans les Caraïbes et les Amériques afin de favoriser le commerce international, les échanges entre jeunes, l’expansion des marchés et le mentorat transfrontalier.",
                ],
            ],
        ];

        foreach ($pillars as $pillar) {
            Program::create(array_merge(['is_active' => true], $pillar));
        }
    }
}
