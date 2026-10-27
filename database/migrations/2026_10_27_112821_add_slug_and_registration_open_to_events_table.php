<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id');
            $table->boolean('registration_open')->default(false)->after('slug');
        });

        // Backfill slugs for existing rows from English title with collision resolution
        $events = DB::table('events')->get();
        $usedSlugs = [];

        foreach ($events as $event) {
            $titleData = json_decode((string) ($event->title ?? ''), true);
            $enTitle = '';

            if (is_array($titleData)) {
                $enTitle = $titleData['en'] ?? (reset($titleData) ?: '');
            } elseif (is_string($titleData)) {
                $enTitle = $titleData;
            }

            $baseSlug = Str::slug($enTitle ?: ('event-'.$event->id));
            if ($baseSlug === '') {
                $baseSlug = 'event-'.$event->id;
            }

            $slug = $baseSlug;
            $suffix = 1;
            while (in_array($slug, $usedSlugs, true)) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            $usedSlugs[] = $slug;
            DB::table('events')->where('id', $event->id)->update(['slug' => $slug]);
        }

        Schema::table('events', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'registration_open']);
        });
    }
};
