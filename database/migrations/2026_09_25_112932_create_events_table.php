<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_upcoming')->default(false);
            $table->json('title');
            $table->json('tag')->nullable();           // e.g. "Upcoming", "Workshop"
            $table->json('description')->nullable();
            // Stores an array of speaker objects, e.g.:
            // [{"name":"Dr. Salewa Olawoye","title":"Director, Harriet Tubman Institute"}, ...]
            $table->json('speakers')->nullable();
            $table->json('date_label')->nullable();    // display string e.g. "March 2025"
            $table->date('event_date')->nullable();
            $table->json('location')->nullable();
            $table->string('thumbnail')->nullable();     // storage/public path
            $table->string('video_url')->nullable();     // recording or promo video
            $table->string('audio_url')->nullable();     // podcast or audio recap
            $table->json('cta_text')->nullable();
            $table->string('cta_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
