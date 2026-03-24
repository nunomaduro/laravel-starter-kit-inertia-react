<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('head_employee_id')->nullable();
            $table->timestamps();

            $table->index('organization_id');
        });

        Schema::create('hr_employees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('position')->nullable();
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('department_id');
        });

        Schema::create('hr_leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('type'); // annual, sick, personal, unpaid
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index('employee_id');
            $table->foreign('approved_by')->references('id')->on('hr_employees')->nullOnDelete();
        });

        // Back-fill department head FK now that hr_employees exists
        Schema::table('hr_departments', function (Blueprint $table): void {
            $table->foreign('head_employee_id')->references('id')->on('hr_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_departments', function (Blueprint $table): void {
            $table->dropForeign(['head_employee_id']);
        });
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_employees');
        Schema::dropIfExists('hr_departments');
    }
};
