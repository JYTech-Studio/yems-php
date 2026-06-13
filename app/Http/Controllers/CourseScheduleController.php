<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSchedule;
use Illuminate\Http\Request;

class CourseScheduleController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $data = $this->validated($request);
        $course->schedules()->create($data);

        return back()->with('status', '已新增固定時段');
    }

    public function update(Request $request, Course $course, CourseSchedule $schedule)
    {
        abort_unless($schedule->course_id === $course->id, 404);
        $schedule->update($this->validated($request));

        return back()->with('status', '已更新時段');
    }

    public function destroy(Course $course, CourseSchedule $schedule)
    {
        abort_unless($schedule->course_id === $course->id, 404);
        $schedule->delete();

        return back()->with('status', '已刪除時段');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'weekday'    => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'room'       => ['nullable', 'string', 'max:255'],
            'note'       => ['nullable', 'string', 'max:255'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
