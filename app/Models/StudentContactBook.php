<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentContactBook extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id', 'course_id', 'lesson_date', 'content',
        'homework', 'is_visible_to_parent', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'lesson_date'          => 'date',
            'is_visible_to_parent' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
