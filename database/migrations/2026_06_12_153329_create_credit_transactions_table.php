<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // credit_transactions — 點數異動稽核日誌（對齊 yems：每筆含異動後餘額快照）
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('enrollment_id')->constrained('enrollments')->restrictOnDelete();
            // tx_type: purchase / check_in / manual_add / manual_deduct / refund / transfer
            $table->string('tx_type');
            $table->integer('amount');          // 正=加點 / 負=扣點
            $table->integer('balance_after');   // 異動後餘額快照
            $table->string('note')->nullable();
            $table->foreignUuid('performed_by')->nullable()->constrained('profiles')->nullOnDelete();
            $table->uuid('reference_id')->nullable(); // 關聯來源（如 attendance_record id）
            // 財務欄位（migration 006）
            $table->integer('list_price_amount')->nullable();
            $table->integer('paid_amount')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->timestamps();

            $table->index('enrollment_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
