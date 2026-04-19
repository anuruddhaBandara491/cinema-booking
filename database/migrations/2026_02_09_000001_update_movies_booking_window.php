<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'booking_dates')) {
                $table->dropColumn('booking_dates');
            }
            if (! Schema::hasColumn('movies', 'booking_window_days')) {
                $table->unsignedInteger('booking_window_days')->default(3)->after('show_times');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'booking_window_days')) {
                $table->dropColumn('booking_window_days');
            }
            if (! Schema::hasColumn('movies', 'booking_dates')) {
                $table->json('booking_dates')->nullable()->after('show_times');
            }
        });
    }
};
