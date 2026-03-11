<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('mails.database.tables.mails', 'mails'), function (Blueprint $table): void {
            $table->string('transport')
                ->nullable()
                ->after('mailer');
        });
    }
};
