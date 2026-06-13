<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // student_contact_books — 學生個人聯絡簿（每筆綁一位學生，migration 009）
        // 與 lesson_logs（班級共用）不同：這是寫給單一學生 / 家長看的
        Schema::create('student_contact_books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->date('lesson_date');
            $table->text('content');
            $table->text('homework')->nullable();
            $table->boolean('is_visible_to_parent')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('profiles')->nullOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'lesson_date']);
            $table->index('course_id');
            $table->index('is_visible_to_parent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_contact_books');
    }
};
