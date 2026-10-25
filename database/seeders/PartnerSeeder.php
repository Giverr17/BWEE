<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Only organisations named in the BWEE brief. Names stay as they are; notes are EN/FR pairs.
     */
    public function run(): void
    {
        Partner::truncate();

        $partners = [
            // Named launch partners, from the brief
            ['name' => 'Harriet Tubman Institute', 'note' => ['en' => 'Host institution at York University', 'fr' => 'Institution d’accueil à l’Université York']],
            ['name' => 'School of Women in Leadership & Business', 'note' => ['en' => 'Launch partner', 'fr' => 'Partenaire du lancement']],
            ['name' => 'York University', 'note' => ['en' => 'Host university', 'fr' => 'Université d’accueil']],
        ];

        foreach ($partners as $i => $p) {
            Partner::create([
                'sort_order' => $i + 1,
                'is_active' => true,
                'name' => $p['name'],
                'logo' => null,
                'url' => null,
                'note' => $p['note'],
            ]);
        }
    }
}
