<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'courier', 'tracking_number', 'shipped_at', 'estimated_arrival', 'status'])]
class Shipment extends Model
{
    public const STATUS_DISIAPKAN = 'disiapkan';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_TIBA = 'tiba';

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'estimated_arrival' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
