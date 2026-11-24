<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Fillable(['user_id', 'total_price', 'status', 'shipping_address', 'shipping_cost', 'notes', 'courier', 'tracking_number', 'shipped_at'])]
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
            'shipped_at' => 'datetime',
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

    /**
     * Tandai pesanan selesai. Dipakai saat pembeli menekan "Pesanan Diterima"
     * dan saat selesai otomatis. COD dibayar saat barang diterima, jadi
     * pembayarannya ikut dikonfirmasi.
     */
    public function markCompleted(): void
    {
        DB::transaction(function () {
            $this->update(['status' => self::STATUS_SELESAI]);

            if ($this->payment?->method === 'cod') {
                $this->payment->update(['status' => Payment::STATUS_DIKONFIRMASI, 'confirmed_at' => now()]);
            }
        });
    }

    /** Tanggal pesanan dikirim akan selesai otomatis (null jika belum dikirim). */
    public function autoCompleteAt(): ?Carbon
    {
        return $this->status === self::STATUS_DIKIRIM && $this->shipped_at
            ? $this->shipped_at->copy()->addDays(config('agritani.auto_complete_days'))
            : null;
    }

    /** Pesanan dikirim yang sudah melewati batas hari tanpa konfirmasi pembeli. */
    public function scopeReadyToAutoComplete(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DIKIRIM)
            ->whereNotNull('shipped_at')
            ->where('shipped_at', '<=', now()->subDays(config('agritani.auto_complete_days')));
    }
}
