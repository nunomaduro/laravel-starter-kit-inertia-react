<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type')->default('user'); // 'user' | 'system'
            $table->string('action'); // 'setting.changed' | 'role.granted' | 'feature.toggled' | 'logo.uploaded'
            $table->string('subject_type')->nullable(); // 'theme_setting' | 'user' | 'role' | 'feature'
            $table->string('subject_id')->nullable(); // setting name, user id, role name, feature key
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
