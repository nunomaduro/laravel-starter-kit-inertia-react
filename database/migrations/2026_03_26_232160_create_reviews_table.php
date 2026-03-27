<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->unique()->constrained('bookings');
            $table->foreignUuid('guest_id')->constrained('users');
            $table->foreignUuid('property_id')->constrained('properties');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->text('host_response')->nullable();
            $table->timestamp('host_responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
