<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_id', 'weekday', 'start_time', 'end_time', 'room', 'note', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function weekdayLabel(): string
    {
        return ['', '週一', '週二', '週三', '週四', '週五', '週六', '週日'][$this->weekday] ?? '';
    }
}
