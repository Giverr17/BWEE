<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

class GalleryItemSeeder extends Seeder
{
    public function run(): void
    {
        GalleryItem::truncate();

        GalleryItem::create([
            'sort_order' => 1,
            'is_active' => true,
            'type' => 'video',
            'title' => [
                'en' => 'BWEE Global Virtual Launch',
                'fr' => 'Lancement mondial en ligne de BWEE',
            ],
            'caption' => [
                'en' => 'From Survival to Strategy: Building, Scaling, and Sustaining Black Women-Owned Businesses. The inaugural BWEE launch event, August 25, 2026.',
                'fr' => '« De la survie à la stratégie : construire, développer et pérenniser les entreprises appartenant à des femmes noires ». Événement inaugural de lancement de BWEE, 25 août 2026.',
            ],
            'photo_path' => null,            // no thumbnail uploaded yet
            'youtube_id' => null,            // [pending] add YouTube video ID once uploaded
            'recorded_at' => '2026-08-25',
        ]);
    }
}
