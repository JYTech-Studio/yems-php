<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LessonLogPhoto extends Model
{
    use HasUuids;

    protected $fillable = ['lesson_log_id', 'storage_path', 'caption', 'sort_order'];

    public function lessonLog(): BelongsTo
    {
        return $this->belongsTo(LessonLog::class);
    }

    /** 對應 yems 的 signed URL；本地用 storage url，部署改 Supabase */
    public function url(): string
    {
        return Storage::disk(config('filesystems.default'))->url($this->storage_path);
    }
}
