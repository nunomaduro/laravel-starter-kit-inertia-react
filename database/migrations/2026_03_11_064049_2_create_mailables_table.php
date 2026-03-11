<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('mails.database.tables.polymorph'), function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(config('mails.models.mail'))
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('mailable');
        });
    }
};
