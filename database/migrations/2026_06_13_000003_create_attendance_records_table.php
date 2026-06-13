<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // attendance_records — 簽到 / 簽退（簽到觸發扣點，簽退僅記錄）
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignUuid('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->string('record_type'); // check_in / check_out
            $table->timestamp('recorded_at')->useCurrent();
            $table->foreignUuid('rfid_card_id')->nullable()->constrained('rfid_cards')->nullOnDelete();
            $table->boolean('is_manual')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'recorded_at']);
            $table->index('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
