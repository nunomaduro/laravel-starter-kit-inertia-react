<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_templates', static function (Blueprint $table): void {
            $table->id();
            $table->string('event')->index();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->json('meta')->nullable();
            $table->json('recipients');
            $table->json('attachments');
            $table->string('delay')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
