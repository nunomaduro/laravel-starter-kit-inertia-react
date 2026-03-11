<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->foreignId('governor_owned_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        DB::table('announcements')->whereNull('governor_owned_by')->whereNotNull('created_by')->update([
            'governor_owned_by' => DB::raw('created_by'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropForeign(['governor_owned_by']);
        });
    }
};
