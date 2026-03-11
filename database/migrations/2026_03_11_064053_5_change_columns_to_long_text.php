<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('mails.database.tables.mails'), function (Blueprint $table): void {
            $table->longText('html')->nullable()->change();
            $table->longText('text')->nullable()->change();
        });

    }

    public function down(): void
    {
        Schema::table(config('mails.database.tables.mails'), function (Blueprint $table): void {
            $table->text('html')->nullable()->change();
            $table->text('text')->nullable()->change();
        });
    }
};
