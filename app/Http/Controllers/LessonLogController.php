<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LessonLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonLogController extends Controller
{
    public function index(Request $request)
    {
        $courseId = $request->query('course');

        $logs = LessonLog::with(['course', 'creator', 'photos'])
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->latest('log_date')
            ->paginate(10)
            ->withQueryString();

        $courses = Course::where('is_active', true)->orderBy('name')->get();

        return view('lesson-logs.index', compact('logs', 'courses', 'courseId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'log_date'  => ['required', 'date'],
            'summary'   => ['required', 'string'],
            'homework'  => ['nullable', 'string'],
            'photos.*'  => ['nullable', 'image', 'max:5120'], // 每張 ≤5MB
        ]);

        $log = LessonLog::create([
            'course_id'  => $data['course_id'],
            'log_date'   => $data['log_date'],
            'summary'    => $data['summary'],
            'homework'   => $data['homework'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        foreach ((array) $request->file('photos', []) as $i => $file) {
            $path = $file->store('lesson-photos', config('filesystems.default'));
            $log->photos()->create(['storage_path' => $path, 'sort_order' => $i]);
        }

        return redirect()->route('lesson-logs.show', $log)->with('status', '已新增聯絡簿');
    }

    public function show(LessonLog $lessonLog)
    {
        $lessonLog->load(['course', 'creator', 'photos']);

        return view('lesson-logs.show', compact('lessonLog'));
    }

    public function destroy(LessonLog $lessonLog)
    {
        foreach ($lessonLog->photos as $photo) {
            Storage::disk(config('filesystems.default'))->delete($photo->storage_path);
        }
        $lessonLog->delete();

        return redirect()->route('lesson-logs.index')->with('status', '已刪除聯絡簿');
    }
}
