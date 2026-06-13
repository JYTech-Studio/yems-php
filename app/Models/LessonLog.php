<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonLog extends Model
{
    use HasUuids;

    protected $fillable = ['course_id', 'log_date', 'summary', 'homework', 'created_by'];

    protected function casts(): array
    {
        return ['log_date' => 'date'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(LessonLogPhoto::class)->orderBy('sort_order');
    }
}
