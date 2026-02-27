<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_exceptions', static function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(MailTemplate::class)->constrained()->cascadeOnDelete();
            $table->json('data');
            $table->string('type');
            $table->string('code');
            $table->text('message');
            $table->string('file');
            $table->integer('line');
            $table->json('preview');
            $table->text('trace');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_exceptions');
    }
};
