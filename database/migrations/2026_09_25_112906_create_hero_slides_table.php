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
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('layout')->default('center'); // center, split, fullbg
            $table->json('eyebrow')->nullable();
            $table->json('heading');
            $table->json('sub_heading')->nullable();
            $table->json('bio')->nullable();
            $table->json('cta_primary_text')->nullable();
            $table->string('cta_primary_url')->nullable();
            $table->json('cta_secondary_text')->nullable();
            $table->string('cta_secondary_url')->nullable();
            $table->string('bg_image')->nullable();      // path in storage/public
            $table->string('bg_image_left')->nullable(); // for split layout
            $table->string('bg_image_right')->nullable();
            $table->string('photo_path_left')->nullable(); // alternative split layout column
            $table->string('photo_path_right')->nullable();
            $table->string('brand_logo')->nullable();    // for center layout
            $table->string('video_url')->nullable();     // optional video background
            $table->string('audio_url')->nullable();     // optional background audio
            $table->string('bg_color')->nullable();      // fallback bg colour
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
