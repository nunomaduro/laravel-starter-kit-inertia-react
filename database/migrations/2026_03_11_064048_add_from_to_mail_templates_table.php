<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_templates', static function (Blueprint $table): void {
            $table->string('from_name')->nullable()->after('attachments');
            $table->string('from_email')->nullable()->after('from_name');
        });
    }

    public function down(): void
    {
        Schema::table('mail_templates', static function (Blueprint $table): void {
            $table->dropColumn('from_name');
            $table->dropColumn('from_email');
        });
    }
};
