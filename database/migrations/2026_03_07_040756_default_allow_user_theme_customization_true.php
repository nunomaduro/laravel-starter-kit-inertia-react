<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set default "allow user theme customization" to true so all users can change
     * appearance unless system or org admin explicitly denies.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('group', 'theme')
            ->where('name', 'allow_user_theme_customization')
            ->update(['payload' => json_encode(true)]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'theme')
            ->where('name', 'allow_user_theme_customization')
            ->update(['payload' => json_encode(false)]);
    }
};
