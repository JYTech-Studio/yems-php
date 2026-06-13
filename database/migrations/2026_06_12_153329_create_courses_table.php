<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // courses — 課程類型（對齊 yems：點數以「包」為單位儲值）
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('credits_per_pack')->default(20); // 每包點數
            $table->integer('price_per_pack')->nullable();     // 每包價格
            $table->string('class_type')->default('group');    // group 團班 / private 個人班（migration 007）
            $table->string('schedule_note')->nullable();       // 課表人工備註（migration 004）
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // course_schedules — 固定上課時段（migration 004）
        Schema::create('course_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 1=週一 … 7=週日
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['course_id', 'weekday', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_schedules');
        Schema::dropIfExists('courses');
    }
};
