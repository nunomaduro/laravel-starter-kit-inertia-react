<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('guest')->after('phone');
            $table->string('avatar')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar');
            $table->decimal('commission_rate', 5, 4)->nullable()->after('bio');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['phone', 'role', 'avatar', 'bio', 'commission_rate']);
        });
    }
};
