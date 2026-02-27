<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $voucherTable = config('vouchers.table', 'vouchers');
        $pivotTable = config('vouchers.pivot_table', 'user_voucher');

        Schema::create($voucherTable, function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('code', 32)->unique();
            $table->morphs('model');
            $table->text('data')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create($pivotTable, function (Blueprint $table) use ($voucherTable): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('voucher_id');
            $table->timestamp('redeemed_at');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('voucher_id')->references('id')->on($voucherTable);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('vouchers.relation_table', 'user_voucher'));
        Schema::dropIfExists(config('vouchers.table', 'vouchers'));
    }
};
