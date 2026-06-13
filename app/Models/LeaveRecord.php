<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id', 'enrollment_id', 'leave_date', 'reason',
        'is_made_up', 'made_up_date', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'leave_date'   => 'date',
            'made_up_date' => 'date',
            'is_made_up'   => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
