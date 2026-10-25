<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            HeroSlideSeeder::class,
            FounderSeeder::class,
            EventSeeder::class,
            PartnerSeeder::class,
            FaqItemSeeder::class,
            SiteSettingSeeder::class,
            ProgramSeeder::class,
            RoadmapItemSeeder::class,
            GalleryItemSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
