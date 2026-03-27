<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('guest_id')->constrained('users');
            $table->foreignUuid('property_id')->constrained('properties');
            $table->foreignUuid('room_type_id')->constrained('room_types');
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('guests_count');
            $table->string('status')->default('pending');
            $table->string('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->unsignedInteger('total_price');
            $table->unsignedInteger('commission_amount');
            $table->unsignedInteger('host_payout');
            $table->json('price_breakdown')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
