<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LeaveRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // all / pending / done

        $leaves = LeaveRecord::with(['student', 'enrollment.course'])
            ->when($status === 'pending', fn ($q) => $q->where('is_made_up', false))
            ->when($status === 'done', fn ($q) => $q->where('is_made_up', true))
            ->orderByDesc('leave_date')
            ->paginate(15)
            ->withQueryString();

        // 新增請假用：列出 enrollment（學生 · 課程）
        $enrollments = Enrollment::with(['student', 'course'])
            ->whereHas('student', fn ($s) => $s->where('role', 'student')->where('is_active', true))
            ->get()->sortBy(fn ($e) => $e->student->name)->values();

        return view('leave.index', compact('leaves', 'enrollments', 'status'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'leave_date'    => ['required', 'date'],
            'reason'        => ['nullable', 'string', 'max:255'],
        ]);

        $enrollment = Enrollment::findOrFail($data['enrollment_id']);

        LeaveRecord::create([
            'student_id'    => $enrollment->student_id,
            'enrollment_id' => $enrollment->id,
            'leave_date'    => $data['leave_date'],
            'reason'        => $data['reason'] ?? null,
            'created_by'    => $request->user()->id,
        ]);

        return back()->with('status', '已登錄請假');
    }

    /** 標記 / 取消補課（對齊 yems PATCH leave-records）。 */
    public function update(Request $request, LeaveRecord $leave)
    {
        $madeUp = $request->boolean('is_made_up');

        $leave->update([
            'is_made_up'   => $madeUp,
            'made_up_date' => $madeUp ? ($request->date('made_up_date') ?? Carbon::today('Asia/Taipei')) : null,
        ]);

        return back()->with('status', $madeUp ? '已標記補課' : '已取消補課標記');
    }

    public function destroy(LeaveRecord $leave)
    {
        $leave->delete();

        return back()->with('status', '已刪除請假紀錄');
    }
}
