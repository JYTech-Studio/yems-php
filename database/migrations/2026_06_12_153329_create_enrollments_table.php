<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // enrollments — 學生 × 課程 = 一個點數帳戶（對齊 yems：點數在 enrollment 上）
        Schema::create('enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('course_id')->constrained('courses')->restrictOnDelete();
            $table->integer('credits_remaining')->default(0); // 此帳戶剩餘點數
            $table->string('current_material')->nullable();    // 點名畫面顯示，如「TOEIC L5」
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'course_id']);
            $table->index('student_id');
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
