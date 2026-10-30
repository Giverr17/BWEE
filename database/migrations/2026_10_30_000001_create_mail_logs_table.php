<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index(); // 'transactional' or 'bulk'
            $table->string('recipient_email');
            $table->string('mailable_class', 200)->nullable();
            $table->timestamp('sent_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
