<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // lesson_logs — 班級共用的每日課程聯絡簿
        Schema::create('lesson_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->date('log_date');
            $table->text('summary');
            $table->text('homework')->nullable();
            $table->foreignUuid('created_by')->constrained('profiles');
            $table->timestamps();

            $table->index(['course_id', 'log_date']);
        });

        // lesson_log_photos — 聯絡簿照片（實體檔存 Supabase Storage，這裡存路徑）
        Schema::create('lesson_log_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lesson_log_id')->constrained('lesson_logs')->cascadeOnDelete();
            $table->string('storage_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('lesson_log_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_log_photos');
        Schema::dropIfExists('lesson_logs');
    }
};
