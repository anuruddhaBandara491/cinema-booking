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
        Schema::create('seat_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->date('show_date');
            $table->string('show_time');
            $table->string('seat_number');

            // Unique session identifier (generated fresh per booking flow)
            $table->string('session_id')->index();

            // Timestamps for lock management
            $table->timestamp('locked_at')->useCurrent();
            $table->timestamp('expires_at')->index()->nullable();
            $table->timestamps();

            // Unique constraint: Only one user can lock a seat at a time
            $table->unique(['movie_id', 'show_date', 'show_time', 'seat_number'], 'unique_active_seat_lock');

            // Indexes for queries
            $table->index(['session_id', 'expires_at']);
            $table->index(['movie_id', 'show_date', 'show_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_locks');
    }
};
