<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_trigger_schedules', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('event_class');
            $table->foreignId('template_id')->nullable()->constrained('mail_templates')->nullOnDelete();
            $table->unsignedInteger('delay_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('feature_flag')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'event_class']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_trigger_schedules');
    }
};
