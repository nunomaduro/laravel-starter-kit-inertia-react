<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('gateway');
            $table->string('gateway_subscription_id')->nullable();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->unsignedTinyInteger('dunning_emails_sent')->default(0);
            $table->timestamp('failed_at');
            $table->timestamp('last_dunning_sent_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'failed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_payment_attempts');
    }
};
