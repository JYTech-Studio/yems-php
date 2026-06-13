<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::withCount(['enrollments', 'schedules'])->orderBy('name')->paginate(12);

        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        Course::create($this->validated($request));

        return redirect()->route('courses.index')->with('status', '已新增課程');
    }

    public function show(Course $course)
    {
        $course->load(['schedules', 'enrollments.student']);
        $upcoming = $course->upcomingSessions(20);

        return view('courses.show', compact('course', 'upcoming'));
    }

    public function edit(Course $course)
    {
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $course->update($this->validated($request));

        return redirect()->route('courses.show', $course)->with('status', '已更新課程');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')->with('status', '已刪除課程');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'credits_per_pack' => ['required', 'integer', 'min:1'],
            'price_per_pack'   => ['nullable', 'integer', 'min:0'],
            'class_type'       => ['required', 'in:group,private'],
            'schedule_note'    => ['nullable', 'string', 'max:255'],
            'is_active'        => ['required', 'boolean'],
        ]);
    }
}
