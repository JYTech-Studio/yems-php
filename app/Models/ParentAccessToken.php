<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ParentAccessToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'parent_id', 'token', 'is_active', 'expires_at', 'last_accessed_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'expires_at'       => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /** 對齊 yems：256-bit base64url token */
    public static function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
