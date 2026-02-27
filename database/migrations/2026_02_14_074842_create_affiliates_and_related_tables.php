<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('affiliate_code')->unique();
            $table->string('status')->default('pending');
            $table->decimal('commission_rate', 5, 2)->default(20.00);
            $table->string('payment_email')->nullable();
            $table->string('payment_method')->default('paypal');
            $table->json('payment_details')->nullable();
            $table->unsignedBigInteger('total_earnings')->default(0);
            $table->unsignedBigInteger('pending_earnings')->default(0);
            $table->unsignedBigInteger('paid_earnings')->default(0);
            $table->unsignedInteger('total_referrals')->default(0);
            $table->unsignedInteger('successful_conversions')->default(0);
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_code']);
            $table->index(['status']);
        });

        Schema::create('affiliate_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referred_organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
        });

        Schema::create('affiliate_payouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliates');
    }
};
