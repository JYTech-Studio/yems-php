<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'enrollment_id', 'tx_type', 'amount', 'balance_after', 'note',
        'performed_by', 'reference_id',
        'list_price_amount', 'paid_amount', 'discount_amount',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function typeLabel(): string
    {
        return match ($this->tx_type) {
            'purchase'      => '購買儲值',
            'check_in'      => '簽到扣點',
            'manual_add'    => '手動加點',
            'manual_deduct' => '手動扣點',
            'refund'        => '退費',
            'transfer'      => '轉課',
            default         => $this->tx_type,
        };
    }
}
