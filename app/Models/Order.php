<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'total_price', 'status', 'shipping_address', 'shipping_cost', 'notes'])]
class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DIPROSES,
        self::STATUS_DIKIRIM,
        self::STATUS_SELESAI,
        self::STATUS_DIBATALKAN,
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /** Pesanan yang berisi produk milik petani tertentu. */
    public function scopeForFarmer(Builder $query, int $farmerId): Builder
    {
        return $query->whereHas('items.product', fn ($q) => $q->where('user_id', $farmerId));
    }

    /** total_price hanya subtotal barang; yang dibayar pembeli = barang + ongkir. */
    public function grandTotal(): float
    {
        return (float) $this->total_price + (float) $this->shipping_cost;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }
}
