<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Changelog\Enums\ChangelogType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('version')->nullable();
            $table->string('type')->default(ChangelogType::Added->value);
            $table->boolean('is_published')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_published');
            $table->index('released_at');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_entries');
    }
};
