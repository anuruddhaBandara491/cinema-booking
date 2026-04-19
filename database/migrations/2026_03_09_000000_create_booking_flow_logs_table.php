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
        Schema::create('booking_flow_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('step_name')->index(); // user_details, movie_selection, ticket_count, seat_selection, payment
            $table->string('action')->index(); // entered_details, selected_movie, selected_ticket_count, selected_seats, payment_attempt, payment_success, payment_failed
            $table->string('user_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('movie_id')->nullable()->index();
            $table->date('show_date')->nullable();
            $table->string('show_time')->nullable();
            $table->integer('ticket_count')->nullable();
            $table->json('selected_seats')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status')->default('success')->index(); // success, failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Add foreign key to movies table
            $table->foreign('movie_id')
                ->references('id')
                ->on('movies')
                ->nullOnDelete();

            // Composite index for searching by session
            $table->index(['session_id', 'created_at']);
            $table->index(['phone_number', 'created_at']);
            $table->index(['email', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_flow_logs');
    }
};
