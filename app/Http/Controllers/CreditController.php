<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function __construct(private CreditService $credits) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $low = $request->boolean('low');

        $enrollments = Enrollment::with(['student', 'course'])
            ->whereHas('student', fn ($s) => $s->where('role', 'student'))
            ->when($q !== '', fn ($query) => $query->whereHas('student', fn ($s) => $s->where('name', 'like', "%{$q}%")))
            ->when($low, fn ($query) => $query->where('credits_remaining', '<=', 3))
            ->orderBy('credits_remaining')
            ->paginate(15)
            ->withQueryString();

        $students = User::students()->where('is_active', true)->orderBy('name')->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        return view('credits.index', compact('enrollments', 'students', 'courses', 'q', 'low'));
    }

    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'course', 'creditTransactions.performer']);

        return view('credits.show', compact('enrollment'));
    }

    /** 儲值（依包數，upsert enrollment） */
    public function purchase(Request $request)
    {
        $data = $request->validate([
            'student_id'  => ['required', 'exists:profiles,id'],
            'course_id'   => ['required', 'exists:courses,id'],
            'packs'       => ['required', 'integer', 'min:1'],
            'paid'        => ['nullable', 'integer', 'min:0'],
            'list_price'  => ['nullable', 'integer', 'min:0'],
            'discount'    => ['nullable', 'integer', 'min:0'],
            'note'        => ['nullable', 'string', 'max:255'],
        ]);

        $student = User::findOrFail($data['student_id']);
        $course = Course::findOrFail($data['course_id']);

        $enrollment = $this->credits->purchase($student, $course, $data['packs'], $request->user(), [
            'paid'       => $data['paid'] ?? null,
            'list_price' => $data['list_price'] ?? null,
            'discount'   => $data['discount'] ?? null,
        ], $data['note'] ?? null);

        return redirect()->route('credits.show', $enrollment)->with('status', '儲值完成');
    }

    /** 手動加 / 扣點 */
    public function adjust(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:add,deduct'],
            'amount'    => ['required', 'integer', 'min:1'],
            'note'      => ['nullable', 'string', 'max:255'],
        ]);

        $delta = $data['direction'] === 'deduct' ? -$data['amount'] : $data['amount'];

        try {
            $this->credits->adjust($enrollment, $delta, $request->user(), $data['note'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('status', '點數已更新');
    }
}
