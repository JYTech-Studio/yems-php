<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasUuids;

    protected $fillable = [
        'name', 'description', 'credits_per_pack', 'price_per_pack',
        'class_type', 'schedule_note', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CourseSchedule::class)->orderBy('weekday')->orderBy('start_time');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function lessonLogs(): HasMany
    {
        return $this->hasMany(LessonLog::class)->latest('log_date');
    }

    public function classTypeLabel(): string
    {
        return $this->class_type === 'private' ? '個人班' : '團班';
    }

    /**
     * 未來 N 天課表預覽（對齊 yems calendar：從 active 固定時段往後推算實際日期）。
     * 回傳 [ ['date'=>Carbon, 'weekday'=>int, 'start_time'=>, 'end_time'=>, 'room'=>, 'note'=>], ... ]
     */
    public function upcomingSessions(int $days = 20): array
    {
        $days = min(max($days, 1), 90);
        $active = $this->schedules->where('is_active', true);
        $today = \Illuminate\Support\Carbon::today('Asia/Taipei');
        $sessions = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $today->copy()->addDays($i);
            $weekday = $date->isoWeekday(); // 1=Mon..7=Sun
            foreach ($active->where('weekday', $weekday) as $sch) {
                $sessions[] = [
                    'date'       => $date->copy(),
                    'weekday'    => $weekday,
                    'start_time' => substr($sch->start_time, 0, 5),
                    'end_time'   => substr($sch->end_time, 0, 5),
                    'room'       => $sch->room,
                    'note'       => $sch->note,
                ];
            }
        }

        usort($sessions, fn ($a, $b) => [$a['date'], $a['start_time']] <=> [$b['date'], $b['start_time']]);

        return $sessions;
    }
}
