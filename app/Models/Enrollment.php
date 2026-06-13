<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id', 'course_id', 'credits_remaining', 'current_material', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class)->latest();
    }

    public function leaveRecords(): HasMany
    {
        return $this->hasMany(LeaveRecord::class);
    }

    public function isLowCredit(): bool
    {
        return $this->credits_remaining <= 3;
    }
}
