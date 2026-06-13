<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // leave_records — 請假紀錄（不扣點，追蹤補課狀態）
        Schema::create('leave_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->date('leave_date');
            $table->string('reason')->nullable();
            $table->boolean('is_made_up')->default(false);
            $table->date('made_up_date')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('profiles')->nullOnDelete();
            $table->timestamps();

            $table->index('student_id');
            $table->index('is_made_up');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_records');
    }
};
