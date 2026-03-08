<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_domains', function (Blueprint $table): void {
            $table->enum('status', [
                'pending_dns', 'dns_verified', 'ssl_provisioning', 'active', 'error', 'expired',
            ])->default('pending_dns')->after('type');
            $table->string('cname_target')->nullable()->after('status');
            $table->string('failure_reason')->nullable()->after('cname_target');
            $table->unsignedTinyInteger('dns_check_attempts')->default(0)->after('failure_reason');
            $table->timestamp('last_dns_check_at')->nullable()->after('dns_check_attempts');
            $table->timestamp('ssl_issued_at')->nullable()->after('last_dns_check_at');
            $table->timestamp('ssl_expires_at')->nullable()->after('ssl_issued_at');
        });
    }

    public function down(): void
    {
        Schema::table('organization_domains', function (Blueprint $table): void {
            $table->dropColumn([
                'status', 'cname_target', 'failure_reason', 'dns_check_attempts',
                'last_dns_check_at', 'ssl_issued_at', 'ssl_expires_at',
            ]);
        });
    }
};
