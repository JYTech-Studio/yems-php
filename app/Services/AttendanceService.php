<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\RfidCard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * RFID 點名服務 — 對齊 yems 的 lib/attendance-service.js + fn_deduct_credit_on_checkin trigger。
 * - 簽到（check_in）會自動扣 1 點並寫稽核紀錄；點數不足整筆 rollback（= trigger RAISE EXCEPTION）。
 * - 簽退（check_out）僅記錄時間，不扣點。
 * - 60 秒內同卡重刷視為 duplicate_scan。
 * - 台北時區日界。
 */
class AttendanceService
{
    private const TZ = 'Asia/Taipei';
    private const DUP_SECONDS = 60;

    /**
     * 模擬掃卡：以 card_uid 找學生，自動判斷簽到 / 簽退，簽到則扣點。
     * 回傳 ['action' => 'check_in'|'check_out'|'duplicate', 'student' => ..., 'enrollment' => ...]
     */
    public function handleScan(string $cardUid): array
    {
        $card = RfidCard::where('card_uid', $cardUid)->where('is_active', true)->first();
        if (! $card) {
            throw new RuntimeException("查無此卡（{$cardUid}）");
        }

        $student = $card->student;
        $now = Carbon::now(self::TZ);

        // 60 秒重複刷卡防呆
        $lastByCard = AttendanceRecord::where('rfid_card_id', $card->id)
            ->latest('recorded_at')->first();
        if ($lastByCard && $lastByCard->recorded_at->diffInSeconds($now) < self::DUP_SECONDS) {
            return ['action' => 'duplicate', 'student' => $student, 'enrollment' => null];
        }

        // 判斷今天是否已簽到未簽退 → 決定這次是簽到還是簽退
        $todayStart = $now->copy()->startOfDay();
        $lastToday = AttendanceRecord::where('student_id', $student->id)
            ->where('recorded_at', '>=', $todayStart)
            ->latest('recorded_at')->first();

        if ($lastToday && $lastToday->record_type === 'check_in') {
            $record = $this->checkOut($student, $lastToday->enrollment, $card);

            return ['action' => 'check_out', 'student' => $student, 'enrollment' => $lastToday->enrollment, 'record' => $record];
        }

        $enrollment = $this->recommendedEnrollment($student);
        if (! $enrollment) {
            throw new RuntimeException("{$student->name} 沒有可用的報名課程");
        }

        $record = $this->checkIn($student, $enrollment, $card);

        return ['action' => 'check_in', 'student' => $student, 'enrollment' => $enrollment, 'record' => $record];
    }

    /**
     * 簽到 + 自動扣 1 點（原子操作，對齊 trigger fn_deduct_credit_on_checkin）。
     */
    public function checkIn(User $student, Enrollment $enrollment, ?RfidCard $card = null, bool $isManual = false): AttendanceRecord
    {
        return DB::transaction(function () use ($student, $enrollment, $card, $isManual) {
            $enrollment = Enrollment::lockForUpdate()->findOrFail($enrollment->id);

            if ($enrollment->credits_remaining <= 0) {
                throw new RuntimeException("{$student->name} 點數不足，無法簽到扣點");
            }

            $record = AttendanceRecord::create([
                'student_id'    => $student->id,
                'enrollment_id' => $enrollment->id,
                'record_type'   => 'check_in',
                'recorded_at'   => Carbon::now(self::TZ),
                'rfid_card_id'  => $card?->id,
                'is_manual'     => $isManual,
            ]);

            // 扣點 + 寫稽核（= trigger 的兩個動作）
            $enrollment->decrement('credits_remaining');
            $enrollment->refresh();

            \App\Models\CreditTransaction::create([
                'enrollment_id' => $enrollment->id,
                'tx_type'       => 'check_in',
                'amount'        => -1,
                'balance_after' => $enrollment->credits_remaining,
                'reference_id'  => $record->id,
                'note'          => '到班簽到自動扣點',
            ]);

            return $record;
        });
    }

    /** 簽退：僅記錄，不扣點。 */
    public function checkOut(User $student, ?Enrollment $enrollment, ?RfidCard $card = null, bool $isManual = false): AttendanceRecord
    {
        return AttendanceRecord::create([
            'student_id'    => $student->id,
            'enrollment_id' => $enrollment?->id,
            'record_type'   => 'check_out',
            'recorded_at'   => Carbon::now(self::TZ),
            'rfid_card_id'  => $card?->id,
            'is_manual'     => $isManual,
        ]);
    }

    /**
     * 今日推薦課程：依 course_schedules + 台北當下時間挑該生 active enrollment。
     * 命中規則：今天 weekday 有排課且現在落在 (start-30min, end+30min)；否則退而取第一個 active enrollment。
     */
    public function recommendedEnrollment(User $student): ?Enrollment
    {
        $now = Carbon::now(self::TZ);
        $weekday = $now->isoWeekday(); // 1=Mon..7=Sun

        $enrollments = $student->enrollments()
            ->where('is_active', true)
            ->where('credits_remaining', '>', 0)
            ->with('course.schedules')
            ->get();

        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->course->schedules as $sch) {
                if (! $sch->is_active || (int) $sch->weekday !== $weekday) {
                    continue;
                }
                $start = Carbon::parse($sch->start_time, self::TZ)->setDate($now->year, $now->month, $now->day)->subMinutes(30);
                $end   = Carbon::parse($sch->end_time, self::TZ)->setDate($now->year, $now->month, $now->day)->addMinutes(30);
                if ($now->between($start, $end)) {
                    return $enrollment;
                }
            }
        }

        return $enrollments->first();
    }
}
