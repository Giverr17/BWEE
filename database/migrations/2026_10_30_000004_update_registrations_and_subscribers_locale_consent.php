<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('event_registrations', 'newsletter_opt_in')) {
                $table->boolean('newsletter_opt_in')->default(false)->after('newsletter_consent');
            }
            if (! Schema::hasColumn('event_registrations', 'consented_at')) {
                $table->timestamp('consented_at')->nullable()->after('newsletter_opt_in');
            }
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            if (! Schema::hasColumn('newsletter_subscribers', 'locale')) {
                $table->string('locale', 5)->default('en')->after('email');
            }
            if (! Schema::hasColumn('newsletter_subscribers', 'source')) {
                $table->string('source', 50)->default('website')->after('locale');
            }
            if (! Schema::hasColumn('newsletter_subscribers', 'consented_at')) {
                $table->timestamp('consented_at')->nullable()->after('subscribed_at');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'locale')) {
                $table->string('locale', 5)->default('en')->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['newsletter_opt_in', 'consented_at']);
        });

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['locale', 'source', 'consented_at']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['locale']);
        });
    }
};
