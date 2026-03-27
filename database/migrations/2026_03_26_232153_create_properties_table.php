<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('host_id')->constrained('users');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('type');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('amenities')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->text('cancellation_policy')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('host_id');
            $table->index('status');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
