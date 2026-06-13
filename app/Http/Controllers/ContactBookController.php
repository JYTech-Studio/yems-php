<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\StudentContactBook;
use App\Models\User;
use Illuminate\Http\Request;

class ContactBookController extends Controller
{
    public function index(Request $request)
    {
        $studentId = $request->query('student');

        $entries = StudentContactBook::with(['student', 'course', 'creator'])
            ->when($studentId, fn ($q) => $q->where('student_id', $studentId))
            ->latest('lesson_date')
            ->paginate(10)
            ->withQueryString();

        $students = User::students()->where('is_active', true)->orderBy('name')->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        return view('contact-books.index', compact('entries', 'students', 'courses', 'studentId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'           => ['required', 'exists:profiles,id'],
            'course_id'            => ['nullable', 'exists:courses,id'],
            'lesson_date'          => ['required', 'date'],
            'content'              => ['required', 'string'],
            'homework'             => ['nullable', 'string'],
            'is_visible_to_parent' => ['sometimes', 'boolean'],
        ]);
        $data['is_visible_to_parent'] = $request->boolean('is_visible_to_parent', true);
        $data['created_by'] = $request->user()->id;

        StudentContactBook::create($data);

        return back()->with('status', '已新增個人聯絡簿');
    }

    public function destroy(StudentContactBook $contactBook)
    {
        $contactBook->delete();

        return back()->with('status', '已刪除');
    }
}
