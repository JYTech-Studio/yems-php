<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CreditTransaction;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * 點數操作服務 — 對齊 yems 的 fn_purchase_credits / fn_adjust_credits RPC。
 * yems 在 Postgres 用 SECURITY DEFINER 函式做原子操作；這裡用 DB transaction 達到同樣的一致性，
 * 並可同時跑在 SQLite（本地）與 Postgres（部署）。
 */
class CreditService
{
    /**
     * 儲值：找出或建立 enrollment，加點，寫稽核紀錄（tx_type=purchase）。
     * 對齊 fn_purchase_credits（含 list_price / paid / discount 三個金額欄位）。
     */
    public function purchase(User $student, Course $course, int $packs, User $performer, array $amounts = [], ?string $note = null): Enrollment
    {
        if ($packs < 1) {
            throw new RuntimeException('儲值包數需 ≥ 1');
        }

        return DB::transaction(function () use ($student, $course, $packs, $performer, $amounts, $note) {
            $enrollment = Enrollment::lockForUpdate()->firstOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                ['credits_remaining' => 0],
            );

            $credits = $packs * $course->credits_per_pack;
            $enrollment->increment('credits_remaining', $credits);

            CreditTransaction::create([
                'enrollment_id'     => $enrollment->id,
                'tx_type'           => 'purchase',
                'amount'            => $credits,
                'balance_after'     => $enrollment->credits_remaining,
                'note'              => $note ?? "儲值 {$packs} 包（{$credits} 點）",
                'performed_by'      => $performer->id,
                'list_price_amount' => $amounts['list_price'] ?? null,
                'paid_amount'       => $amounts['paid'] ?? null,
                'discount_amount'   => $amounts['discount'] ?? null,
            ]);

            return $enrollment;
        });
    }

    /**
     * 手動加 / 扣點：delta 正=manual_add，負=manual_deduct。對齊 fn_adjust_credits。
     */
    public function adjust(Enrollment $enrollment, int $delta, User $performer, ?string $note = null): Enrollment
    {
        if ($delta === 0) {
            throw new RuntimeException('調整點數不可為 0');
        }

        return DB::transaction(function () use ($enrollment, $delta, $performer, $note) {
            $enrollment = Enrollment::lockForUpdate()->findOrFail($enrollment->id);

            if ($enrollment->credits_remaining + $delta < 0) {
                throw new RuntimeException('點數不足，無法扣點');
            }

            $enrollment->increment('credits_remaining', $delta);

            CreditTransaction::create([
                'enrollment_id' => $enrollment->id,
                'tx_type'       => $delta > 0 ? 'manual_add' : 'manual_deduct',
                'amount'        => $delta,
                'balance_after' => $enrollment->credits_remaining,
                'note'          => $note,
                'performed_by'  => $performer->id,
            ]);

            return $enrollment;
        });
    }
}
