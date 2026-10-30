<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $missing = [
            [
                'key' => 'nav_cta',
                'value' => 'Partner & Sponsor',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Navigation CTA Button',
            ],
            [
                'key' => 'nav_cta_fr',
                'value' => 'Devenir partenaire',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Navigation CTA Button (French)',
            ],
            [
                'key' => 'site_name_fr',
                'value' => "Échange d'entreprises pour femmes noires (BWEE)",
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Name (French)',
            ],
            [
                'key' => 'contact_email_fr',
                'value' => 'info@bweeofficial.com',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Contact Email (French)',
            ],
            [
                'key' => 'involve_member_url_fr',
                'value' => 'mailto:info@bweeofficial.com?subject=Interet%20adhesion',
                'type' => 'text',
                'group' => 'involved',
                'label' => 'Member Card Link (French)',
            ],
            [
                'key' => 'involve_sponsor_url_fr',
                'value' => 'mailto:info@bweeofficial.com?subject=Partenariat%20ou%20commandite',
                'type' => 'text',
                'group' => 'involved',
                'label' => 'Sponsor Card Link (French)',
            ],
            [
                'key' => 'involve_volunteer_url_fr',
                'value' => 'mailto:info@bweeofficial.com?subject=Benevole%20ou%20mentor',
                'type' => 'text',
                'group' => 'involved',
                'label' => 'Volunteer Card Link (French)',
            ],
        ];

        foreach ($missing as $item) {
            SiteSetting::firstOrCreate(
                ['key' => $item['key']],
                $item
            );
        }
    }

    public function down(): void
    {
        SiteSetting::whereIn('key', [
            'nav_cta',
            'nav_cta_fr',
            'site_name_fr',
            'contact_email_fr',
            'involve_member_url_fr',
            'involve_sponsor_url_fr',
            'involve_volunteer_url_fr',
        ])->delete();
    }
};

