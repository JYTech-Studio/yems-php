<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LeaveRecord;
use App\Models\ParentAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * 家長 Portal — 對齊 yems：無登入，靠 token 驗身分。
 */
class ParentPortalController extends Controller
{
    /** 由 token 反查家長，並更新 last_accessed_at；失效則 404。 */
    private function resolveParent(string $token): User
    {
        $access = ParentAccessToken::where('token', $token)->where('is_active', true)->first();
        abort_unless($access, 404, '連結無效或已失效');
        $access->update(['last_accessed_at' => now()]);

        return $access->parent;
    }

    public function home(string $token)
    {
        $parent = $this->resolveParent($token);
        $children = $parent->children()->where('is_active', true)
            ->with(['enrollments.course'])->get();

        return view('portal.home', compact('parent', 'children', 'token'));
    }

    public function student(string $token, User $student)
    {
        $parent = $this->resolveParent($token);
        // 僅能看自己的孩子
        abort_unless($parent->children()->where('profiles.id', $student->id)->exists(), 403);

        $student->load([
            'enrollments.course.schedules',
            'attendanceRecords' => fn ($q) => $q->with('enrollment.course')->take(20),
            'leaveRecords.enrollment.course',
        ]);

        // 固定課表（彙整所有 enrollment 的 active 課表）
        $schedule = [];
        foreach ($student->enrollments as $e) {
            foreach ($e->course->schedules->where('is_active', true) as $s) {
                $schedule[] = ['course' => $e->course->name, 'weekday' => $s->weekday,
                    'start' => substr($s->start_time, 0, 5), 'end' => substr($s->end_time, 0, 5), 'room' => $s->room];
            }
        }
        usort($schedule, fn ($a, $b) => [$a['weekday'], $a['start']] <=> [$b['weekday'], $b['start']]);

        $contactBooks = \App\Models\StudentContactBook::with('course')
            ->where('student_id', $student->id)
            ->where('is_visible_to_parent', true)
            ->latest('lesson_date')->take(20)->get();

        return view('portal.student', compact('parent', 'student', 'token', 'schedule', 'contactBooks'));
    }

    /** 線上請假 */
    public function leaveStore(Request $request, string $token, User $student)
    {
        $parent = $this->resolveParent($token);
        abort_unless($parent->children()->where('profiles.id', $student->id)->exists(), 403);

        $data = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'leave_date'    => ['required', 'date'],
            'reason'        => ['nullable', 'string', 'max:255'],
        ]);

        $enrollment = Enrollment::where('id', $data['enrollment_id'])->where('student_id', $student->id)->firstOrFail();

        LeaveRecord::create([
            'student_id'    => $student->id,
            'enrollment_id' => $enrollment->id,
            'leave_date'    => $data['leave_date'],
            'reason'        => $data['reason'] ?? null,
            'created_by'    => $parent->id,
        ]);

        return back()->with('status', '已送出請假申請');
    }
}
