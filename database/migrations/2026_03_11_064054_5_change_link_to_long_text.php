<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('mails.database.tables.events', 'mail_events'), function (Blueprint $table): void {
            $table->longText('link')->nullable()->change();
        });

    }

    public function down(): void
    {
        Schema::table(config('mails.database.tables.events', 'mail_events'), function (Blueprint $table): void {
            $table->string('link')->nullable()->change();
        });
    }
};
