<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\RfidCard;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance) {}

    public function index()
    {
        $todayRecords = AttendanceRecord::with(['student', 'enrollment.course'])
            ->where('recorded_at', '>=', Carbon::today('Asia/Taipei'))
            ->latest('recorded_at')
            ->take(30)
            ->get();

        // 模擬刷卡用：列出有效卡片（含學生名）讓操作者快速點選
        $cards = RfidCard::with('student')->where('is_active', true)
            ->get()->sortBy('student.name')->values();

        return view('attendance.index', compact('todayRecords', 'cards'));
    }

    public function scan(Request $request)
    {
        $data = $request->validate(['card_uid' => ['required', 'string']]);

        try {
            $result = $this->attendance->handleScan(trim($data['card_uid']));
        } catch (\RuntimeException $e) {
            return back()->with('scan_error', $e->getMessage());
        }

        $student = $result['student'];
        $enrollment = $result['enrollment'];

        $msg = match ($result['action']) {
            'check_in'  => "✅ {$student->name} 簽到成功 · {$enrollment->course->name} · 扣 1 點，剩 {$enrollment->fresh()->credits_remaining} 點",
            'check_out' => "👋 {$student->name} 簽退成功",
            'duplicate' => "⚠️ {$student->name} 60 秒內重複刷卡，已忽略",
            default     => "{$student->name}",
        };

        return back()->with('scan_result', ['action' => $result['action'], 'message' => $msg]);
    }
}
