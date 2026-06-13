<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // student_parents — 家長 ↔ 學生 多對多
        Schema::create('student_parents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('parent_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('relation')->default('家長');
            $table->timestamps();

            $table->unique(['student_id', 'parent_id']);
            $table->index('student_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parents');
    }
};
