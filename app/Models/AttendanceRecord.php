<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id', 'enrollment_id', 'record_type',
        'recorded_at', 'rfid_card_id', 'is_manual', 'note',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'is_manual'   => 'boolean',
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

    public function rfidCard(): BelongsTo
    {
        return $this->belongsTo(RfidCard::class);
    }

    public function typeLabel(): string
    {
        return $this->record_type === 'check_in' ? '簽到' : '簽退';
    }
}
