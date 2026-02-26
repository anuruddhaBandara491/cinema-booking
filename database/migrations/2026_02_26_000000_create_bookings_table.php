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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->string('booking_reference')->unique(); // Unique booking reference
            
            // User details from booking form
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('customer_nic')->nullable();
            
            // Booking details (JSON for flexibility)
            $table->date('booking_date');
            $table->string('booking_time');
            $table->json('selected_seats'); // Array of seat numbers
            $table->json('tickets'); // Ticket quantities by type
            $table->decimal('total_amount', 10, 2);
            
            // Payment information
            $table->string('payment_method'); // card, wallet, cash
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_reference')->nullable();
            
            // Timestamps
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
            
            // Indexes
            $table->index('movie_id');
            $table->index('customer_phone');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
