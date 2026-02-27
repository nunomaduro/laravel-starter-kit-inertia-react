<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_scopes', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('voucher_scopes')->insert([
            'name' => 'Global',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_scopes');
    }
};
