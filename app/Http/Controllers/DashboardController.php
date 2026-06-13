<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students'    => User::students()->where('is_active', true)->count(),
            'courses'     => Course::where('is_active', true)->count(),
            'today_checkins' => \App\Models\AttendanceRecord::where('record_type', 'check_in')
                ->where('recorded_at', '>=', Carbon::today('Asia/Taipei'))->count(),
            'low_credit'  => Enrollment::where('is_active', true)->where('credits_remaining', '<=', 3)->count(),
        ];

        // 點數不足的帳戶（enrollment 層級）
        $lowCreditEnrollments = Enrollment::with(['student', 'course'])
            ->where('is_active', true)
            ->where('credits_remaining', '<=', 3)
            ->whereHas('student', fn ($q) => $q->where('is_active', true))
            ->orderBy('credits_remaining')
            ->take(10)
            ->get();

        return view('dashboard', compact('stats', 'lowCreditEnrollments'));
    }
}
