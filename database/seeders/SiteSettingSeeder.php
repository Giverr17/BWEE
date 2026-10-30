<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Text is taken from the BWEE bilingual brief. The base key holds English;
     * its French twin is the same key with a _fr suffix (read it with the current locale, falling back to English).
     */
    public function run(): void
    {
        SiteSetting::truncate();

        $settings = [
            ['key' => 'site_name', 'value' => 'Black Women’s Enterprise Exchange (BWEE)', 'type' => 'text', 'group' => 'general', 'label' => 'Site Name'],
            ['key' => 'site_tagline', 'value' => 'Building Capital, Strategy, and Transnational Economic Power', 'type' => 'text', 'group' => 'general', 'label' => 'Tagline'],
            ['key' => 'site_tagline_fr', 'value' => 'Construction du capital, de la stratégie et de la puissance économique transnationale', 'type' => 'text', 'group' => 'general', 'label' => 'Tagline (French)'],
            ['key' => 'site_subtitle', 'value' => 'A Research-Informed, Dual-Language Pilot of the Harriet Tubman Institute, York University', 'type' => 'text', 'group' => 'general', 'label' => 'Subtitle'],
            ['key' => 'site_subtitle_fr', 'value' => 'Un projet pilote bilingue fondé sur la recherche, de l’Institut Harriet Tubman de l’Université York.', 'type' => 'text', 'group' => 'general', 'label' => 'Subtitle (French)'],
            ['key' => 'site_logo', 'value' => 'images/bwee-logo.svg', 'type' => 'image', 'group' => 'general', 'label' => 'Header / Footer Logo'],
            ['key' => 'contact_email', 'value' => 'info@bweeofficial.com', 'type' => 'text', 'group' => 'general', 'label' => 'Contact Email (joint BWEE email)'],
            ['key' => 'mission_title', 'value' => 'Our Mission', 'type' => 'text', 'group' => 'mission', 'label' => 'Mission Title'],
            ['key' => 'mission_title_fr', 'value' => 'Notre mission', 'type' => 'text', 'group' => 'mission', 'label' => 'Mission Title (French)'],
            ['key' => 'mission_paragraph_1', 'value' => 'The Black Women’s Enterprise Exchange (BWEE) is a premier Canadian community, research-informed, and networking platform dedicated to the economic advancement, leadership development, skills acquisition, and enterprise scaling of Black women, young women, and youth across Canada.', 'type' => 'text', 'group' => 'mission', 'label' => 'Mission Paragraph 1'],
            ['key' => 'mission_paragraph_1_fr', 'value' => 'Le Black Women’s Enterprise Exchange (BWEE) est une plateforme communautaire canadienne de premier plan, fondée sur la recherche et axée sur le réseautage, qui se consacre au progrès économique, au développement du leadership, à l’acquisition de compétences et à la croissance des entreprises des femmes noires, des jeunes femmes et des jeunes à travers le Canada.', 'type' => 'text', 'group' => 'mission', 'label' => 'Mission Paragraph 1 (French)'],
            ['key' => 'mission_paragraph_2', 'value' => "BWEE equips Black women entrepreneurs, small business owners, young women, and students across Canada with actionable business intelligence, access to non-dilutive capital, strategic executive mentorship, practical vocational training, cutting-edge AI and digital skills, and cross-border networks.\n\nGrounded in anti-racist principles and dual-language (English–French) accessibility, we foster an early entrepreneurial mindset in youth, rapidly integrate newcomer and immigrant women into the Canadian economic system, drive policy reform through academic research, and build lasting financial independence across generations.", 'type' => 'text', 'group' => 'mission', 'label' => 'Mission Paragraph 2'],
            ['key' => 'mission_paragraph_2_fr', 'value' => "BWEE accompagne les entrepreneures noires, les propriétaires de petites entreprises, les jeunes femmes et les étudiantes partout au Canada en leur donnant accès à des connaissances commerciales pratiques, à des financements non dilutifs, à un mentorat stratégique de haut niveau, à des formations professionnelles concrètes, à des compétences avancées en intelligence artificielle (IA) et en technologies numériques, ainsi qu’à des réseaux transfrontaliers.\n\nFondée sur des principes antiracistes et sur une accessibilité bilingue (anglais-français), nous encourageons le développement à l’esprit entrepreneurial chez les jeunes, l’intégration rapide des femmes nouvellement arrivées et immigrantes dans le système économique canadien, la réforme des politiques publiques grâce à la recherche universitaire et une indépendance financière durable d’une génération à l’autre.", 'type' => 'text', 'group' => 'mission', 'label' => 'Mission Paragraph 2 (French)'],
            ['key' => 'mission_affiliation', 'value' => 'Harriet Tubman Institute for Research on Africa and Its Diaspora, York University, Toronto, Ontario, Canada', 'type' => 'text', 'group' => 'mission', 'label' => 'Host Affiliation'],
            ['key' => 'mission_affiliation_fr', 'value' => 'Institut Harriet Tubman pour la recherche sur l’Afrique et sa diaspora, Université York, Toronto, Ontario, Canada', 'type' => 'text', 'group' => 'mission', 'label' => 'Host Affiliation (French)'],
            ['key' => 'about_paragraph_2', 'value' => 'Grounded in doctoral and postdoctoral research on Black women entrepreneurs in Toronto and across international diaspora contexts, BWEE addresses deep-seated systemic economic barriers to turn individual resilience into collective economic power. A pilot of the Harriet Tubman Institute for Research on Africa and Its Diaspora at York University, BWEE serves as an institutional home for Black female entrepreneurs, small business owners, professionals, emerging youth leaders, students, scholars, and community changemakers.', 'type' => 'text', 'group' => 'about', 'label' => 'About Paragraph 2'],
            ['key' => 'about_paragraph_2_fr', 'value' => 'S’appuyant sur des recherches doctorales et postdoctorales menées auprès de femmes entrepreneurs noires à Toronto et dans divers contextes de la diaspora internationale, le BWEE examine les obstacles économiques systémiques profondément enracinés afin de transformer la résilience individuelle en puissance économique collective. Projet pilote de l’Institut Harriet Tubman pour la recherche sur l’Afrique et sa diaspora à l’Université York, le BWEE se considère comme une attache institutionnelle aux femmes entrepreneurs noires, propriétaires de petites entreprises, professionnelles, jeunes leaders émergentes, étudiantes, aux chercheures et aux actrices du changement au sein de la communauté.', 'type' => 'text', 'group' => 'about', 'label' => 'About Paragraph 2 (French)'],
            ['key' => 'about_paragraph_3', 'value' => 'We are explicitly committed to Equity, Diversity, and Inclusion (EDI) and Anti-Racism. Our platform provides tailored structural support for Black women and youth with disabilities, and ensures full inclusion and advocacy for Black LGBTQ+ women and youth navigating the small business landscape.', 'type' => 'text', 'group' => 'about', 'label' => 'About Paragraph 3 (EDI)'],
            ['key' => 'about_paragraph_3_fr', 'value' => 'Nous sommes profondément engagés en faveur de l’équité, de la diversité et de l’inclusion (EDI) ainsi que dans la lutte contre le racisme. Notre plateforme offre un soutien structurel sur mesure aux femmes noires et aux jeunes en situation de handicap, et garantit une inclusion totale ainsi qu’un soutien aux femmes et aux jeunes LGBTQ+ noirs qui évoluent dans le monde de la petite entreprise.', 'type' => 'text', 'group' => 'about', 'label' => 'About Paragraph 3 (EDI) (French)'],
            ['key' => 'vision_title', 'value' => 'Our Vision', 'type' => 'text', 'group' => 'about', 'label' => 'Vision Title'],
            ['key' => 'vision_title_fr', 'value' => 'Notre vision', 'type' => 'text', 'group' => 'about', 'label' => 'Vision Title (French)'],
            ['key' => 'vision_text', 'value' => 'To build an equitable, inclusive, anti-racist, and globally connected economic ecosystem where Black women, young women, students, and youth across Canada, including newcomers, immigrant women, individuals with disabilities, and LGBTQ+ youth, transition from necessity-driven survival into strategic growth, early entrepreneurial readiness, long-term wealth creation, and global economic leadership.', 'type' => 'text', 'group' => 'about', 'label' => 'Vision Text'],
            ['key' => 'vision_text_fr', 'value' => 'Construire un écosystème économique équitable, inclusif, antiraciste et connecté à l’échelle mondiale, au sein duquel les femmes noires, les jeunes femmes, les étudiantes et les jeunes de tout le Canada y compris les nouveaux arrivants, les femmes immigrées, les personnes en situation de handicap et les jeunes LGBTQ+ puissent passer d’un mode de survie dicté par la nécessité à une croissance stratégique, à une préparation précoce à l’entrepreneuriat, à la création de richesse à long terme et au leadership économique mondial.', 'type' => 'text', 'group' => 'about', 'label' => 'Vision Text (French)'],
            ['key' => 'stat_1_number', 'value' => '172', 'type' => 'text', 'group' => 'stats', 'label' => 'Inaugural Launch Registrants'],
            ['key' => 'stat_1_label', 'value' => 'Registered participants', 'type' => 'text', 'group' => 'stats', 'label' => 'Stat 1 Label'],
            ['key' => 'stat_1_label_fr', 'value' => 'Participants inscrits', 'type' => 'text', 'group' => 'stats', 'label' => 'Stat 1 Label (French)'],
            ['key' => 'stat_2_number', 'value' => '62', 'type' => 'text', 'group' => 'stats', 'label' => 'Canadian Registrants'],
            ['key' => 'stat_2_label', 'value' => 'Canadian registrants across 8 provinces (none yet established)', 'type' => 'text', 'group' => 'stats', 'label' => 'Stat 2 Label'],
            ['key' => 'stat_2_label_fr', 'value' => 'Inscrits canadiens dans 8 provinces (aucun encore établi)', 'type' => 'text', 'group' => 'stats', 'label' => 'Stat 2 Label (French)'],
            ['key' => 'stat_3_number', 'value' => '13', 'type' => 'text', 'group' => 'stats', 'label' => 'Countries Represented'],
            ['key' => 'stat_3_label', 'value' => 'Countries represented', 'type' => 'text', 'group' => 'stats', 'label' => 'Stat 3 Label'],
            ['key' => 'stat_3_label_fr', 'value' => 'Pays représentés', 'type' => 'text', 'group' => 'stats', 'label' => 'Stat 3 Label (French)'],
            ['key' => 'launch_date', 'value' => 'August 25, 2026', 'type' => 'text', 'group' => 'launch', 'label' => 'Launch Date'],
            ['key' => 'launch_date_fr', 'value' => '25 août 2026', 'type' => 'text', 'group' => 'launch', 'label' => 'Launch Date (French)'],
            ['key' => 'launch_format', 'value' => 'Virtual', 'type' => 'text', 'group' => 'launch', 'label' => 'Launch Format'],
            ['key' => 'launch_format_fr', 'value' => 'En ligne', 'type' => 'text', 'group' => 'launch', 'label' => 'Launch Format (French)'],
            ['key' => 'launch_theme', 'value' => 'From Survival to Strategy: Building, Scaling, and Sustaining Black Women-Owned Businesses', 'type' => 'text', 'group' => 'launch', 'label' => 'Launch Theme'],
            ['key' => 'launch_theme_fr', 'value' => 'De la survie à la stratégie : construire, développer et pérenniser les entreprises appartenant à des femmes noires', 'type' => 'text', 'group' => 'launch', 'label' => 'Launch Theme (French)'],
            ['key' => 'involve_intro', 'value' => 'BWEE relies on collective action, community collaboration, and strategic partnerships. We invite entrepreneurs, students, youth, institutions, and advocates to join our vision:', 'type' => 'text', 'group' => 'involved', 'label' => 'Get Involved Intro'],
            ['key' => 'involve_intro_fr', 'value' => 'BWEE repose sur l’action collective, la collaboration communautaire et les partenariats stratégiques. Nous invitons les entrepreneures, les étudiants, les jeunes, les institutions et les personnes engagées dans la défense des droits à prendre part à notre vision.', 'type' => 'text', 'group' => 'involved', 'label' => 'Get Involved Intro (French)'],
            ['key' => 'involve_member_title', 'value' => 'Join as a Member / Youth / Student', 'type' => 'text', 'group' => 'involved', 'label' => 'Member Card Title'],
            ['key' => 'involve_member_title_fr', 'value' => 'Devenir membre, jeune membre ou étudiant membre', 'type' => 'text', 'group' => 'involved', 'label' => 'Member Card Title (French)'],
            ['key' => 'involve_member_text', 'value' => 'Register to access our upcoming vocational trades bootcamps, AI skill workshops, grant notices, and bilingual networking events.', 'type' => 'text', 'group' => 'involved', 'label' => 'Member Card Text'],
            ['key' => 'involve_member_text_fr', 'value' => 'Inscrivez-vous pour accéder à nos prochaines formations professionnelles, à nos ateliers sur l’intelligence artificielle, aux annonces de subventions et aux événements de réseautage bilingues.', 'type' => 'text', 'group' => 'involved', 'label' => 'Member Card Text (French)'],
            ['key' => 'involve_member_cta', 'value' => 'Register Interest →', 'type' => 'text', 'group' => 'involved', 'label' => 'Member Card CTA'],
            ['key' => 'involve_member_cta_fr', 'value' => 'Manifester son intérêt →', 'type' => 'text', 'group' => 'involved', 'label' => 'Member Card CTA (French)'],
            ['key' => 'involve_member_url', 'value' => 'mailto:info@bweeofficial.com?subject=Membership%20Interest', 'type' => 'text', 'group' => 'involved', 'label' => 'Member Card Link'],
            ['key' => 'involve_sponsor_title', 'value' => 'Partner & Sponsor', 'type' => 'text', 'group' => 'involved', 'label' => 'Sponsor Card Title'],
            ['key' => 'involve_sponsor_title_fr', 'value' => 'Devenir partenaire ou commanditaire', 'type' => 'text', 'group' => 'involved', 'label' => 'Sponsor Card Title (French)'],
            ['key' => 'involve_sponsor_text', 'value' => 'Collaborate with BWEE to support our EDI, anti-racism, youth enterprise, newcomer integration, and vocational trades initiatives through corporate sponsorship, grant funding, or resource sharing.', 'type' => 'text', 'group' => 'involved', 'label' => 'Sponsor Card Text'],
            ['key' => 'involve_sponsor_text_fr', 'value' => 'Collaborez avec BWEE pour soutenir les initiatives d’équité, de diversité et d’inclusion, de lutte contre le racisme, d’entrepreneuriat jeunesse, d’intégration des personnes nouvellement arrivées et de formation professionnelle, grâce au parrainage d’entreprises, aux subventions ou au partage de ressources.', 'type' => 'text', 'group' => 'involved', 'label' => 'Sponsor Card Text (French)'],
            ['key' => 'involve_sponsor_cta', 'value' => 'Partnership Inquiries →', 'type' => 'text', 'group' => 'involved', 'label' => 'Sponsor Card CTA'],
            ['key' => 'involve_sponsor_cta_fr', 'value' => 'Demandes de partenariat →', 'type' => 'text', 'group' => 'involved', 'label' => 'Sponsor Card CTA (French)'],
            ['key' => 'involve_sponsor_url', 'value' => 'mailto:info@bweeofficial.com?subject=Partnership%20or%20Sponsorship', 'type' => 'text', 'group' => 'involved', 'label' => 'Sponsor Card Link'],
            ['key' => 'involve_volunteer_title', 'value' => 'Volunteer & Mentor', 'type' => 'text', 'group' => 'involved', 'label' => 'Volunteer Card Title'],
            ['key' => 'involve_volunteer_title_fr', 'value' => 'Devenir bénévole ou mentor', 'type' => 'text', 'group' => 'involved', 'label' => 'Volunteer Card Title (French)'],
            ['key' => 'involve_volunteer_text', 'value' => 'Share your expertise as an executive mentor, AI/tech facilitator, vocational trainer, or volunteer to help empower the next generation of Black women and youth leaders across Canada.', 'type' => 'text', 'group' => 'involved', 'label' => 'Volunteer Card Text'],
            ['key' => 'involve_volunteer_text_fr', 'value' => 'Partagez votre expertise en tant que mentor de direction, formateur en IA et technologies, formateur professionnel ou bénévole afin de contribuer à l’autonomisation de la prochaine génération de femmes noires et de jeunes leaders au Canada.', 'type' => 'text', 'group' => 'involved', 'label' => 'Volunteer Card Text (French)'],
            ['key' => 'involve_volunteer_cta', 'value' => 'Volunteer With Us →', 'type' => 'text', 'group' => 'involved', 'label' => 'Volunteer Card CTA'],
            ['key' => 'involve_volunteer_cta_fr', 'value' => 'Devenir bénévole →', 'type' => 'text', 'group' => 'involved', 'label' => 'Volunteer Card CTA (French)'],
            ['key' => 'involve_volunteer_url', 'value' => 'mailto:info@bweeofficial.com?subject=Volunteer%20or%20Mentor', 'type' => 'text', 'group' => 'involved', 'label' => 'Volunteer Card Link'],
            ['key' => 'mail_daily_limit', 'value' => '400', 'type' => 'text', 'group' => 'mail', 'label' => 'Daily email send limit (safe cap, default 400)'],
            ['key' => 'contact_notification_emails', 'value' => 'misibor@yorku.ca,adjoufan@yorku.ca', 'type' => 'text', 'group' => 'contact', 'label' => 'Who receives new contact-form notifications (comma-separated)'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::create($setting);
        }
    }
}
