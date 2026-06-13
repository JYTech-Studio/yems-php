<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // rfid_cards — 學生 RFID 卡片（一人多卡：悠遊卡 / 一卡通 / 貼紙）
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('card_uid')->unique();
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('card_uid');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_cards');
    }
};
