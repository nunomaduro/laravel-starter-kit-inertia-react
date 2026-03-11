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
            $table->unsignedInteger('position')->nullable()->after('is_active');
        });

        $ids = DB::table('announcements')->orderBy('id')->pluck('id');
        foreach ($ids as $index => $id) {
            DB::table('announcements')->where('id', $id)->update(['position' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropColumn('position');
        });
    }
};
