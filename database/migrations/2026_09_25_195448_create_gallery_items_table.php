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
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->json('title')->nullable();
            $table->json('caption')->nullable();
            $table->string('photo_path')->nullable();  // cover image path
            $table->json('photos')->nullable();           // ordered array of storage paths for album
            $table->string('youtube_id')->nullable();  // YouTube video ID for embeds
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->date('recorded_at')->nullable();   // date of event/recording
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
