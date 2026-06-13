<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * profiles — 所有角色共用（admin / teacher / parent / student）
 * 對齊 yems：學生為 role='student' 且無密碼，不需登入。
 */
class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'profiles';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'role', 'name', 'phone', 'email', 'grade_level',
        'avatar_url', 'is_active', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // === 角色判斷 ===
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isTeacher(): bool { return $this->role === 'teacher'; }
    public function isStaff(): bool   { return in_array($this->role, ['admin', 'teacher']); }
    public function isParent(): bool  { return $this->role === 'parent'; }
    public function isStudent(): bool { return $this->role === 'student'; }

    // === Scopes ===
    public function scopeStudents($q)    { return $q->where('role', 'student'); }
    public function scopeParentUsers($q) { return $q->where('role', 'parent'); }
    public function scopeStaff($q)       { return $q->whereIn('role', ['admin', 'teacher']); }

    // === 關聯（以學生身分）===
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function rfidCards(): HasMany
    {
        return $this->hasMany(RfidCard::class, 'student_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id')->latest('recorded_at');
    }

    public function leaveRecords(): HasMany
    {
        return $this->hasMany(LeaveRecord::class, 'student_id');
    }

    /** 此學生的家長們 */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_parents', 'student_id', 'parent_id')
            ->withPivot('relation')->withTimestamps();
    }

    // === 關聯（以家長身分）===
    /** 此家長的孩子們 */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_parents', 'parent_id', 'student_id')
            ->withPivot('relation')->withTimestamps();
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(ParentAccessToken::class, 'parent_id');
    }
}
