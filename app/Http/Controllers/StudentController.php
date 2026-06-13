<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $students = User::students()
            ->withCount(['enrollments', 'rfidCards'])
            ->when($q !== '', fn ($query) => $query->where(fn ($w) =>
                $w->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('students.index', compact('students', 'q'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $student = User::create($this->validated($request) + ['role' => 'student']);

        return redirect()->route('students.show', $student)->with('status', '已新增學生');
    }

    public function show(User $student)
    {
        abort_unless($student->isStudent(), 404);
        $student->load([
            'rfidCards',
            'enrollments.course',
            'parents',
            'attendanceRecords' => fn ($q) => $q->with('enrollment.course')->take(10),
            'leaveRecords.enrollment.course',
        ]);
        $student->loadCount('attendanceRecords');

        return view('students.show', compact('student'));
    }

    public function edit(User $student)
    {
        abort_unless($student->isStudent(), 404);

        return view('students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);
        $student->update($this->validated($request));

        return redirect()->route('students.show', $student)->with('status', '已更新學生資料');
    }

    public function destroy(User $student)
    {
        abort_unless($student->isStudent(), 404);
        $student->delete();

        return redirect()->route('students.index')->with('status', '已刪除學生');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'grade_level' => ['nullable', 'string', 'max:50'],
            'is_active'   => ['required', 'boolean'],
        ]);
    }
}
