<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'category_id', 'name', 'slug', 'description', 'price', 'stock', 'unit', 'weight', 'image', 'is_active'])]
class Product extends Model
{
    public const LOW_STOCK_THRESHOLD = 10;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'weight' => 'integer',
            'is_active' => 'boolean',
            'is_suspended' => 'boolean',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /** Produk yang tampil & bisa dibeli: aktif, tidak diturunkan admin, petaninya aktif. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_suspended', false)
            ->whereHas('farmer', fn ($f) => $f->where('is_active', true));
    }

    public function isPurchasable(): bool
    {
        return $this->is_active && ! $this->is_suspended && (bool) $this->farmer?->is_active;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', self::LOW_STOCK_THRESHOLD);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $term ? $query->where('name', 'like', "%{$term}%") : $query;
    }

    /** Sort: termurah, termahal, atau terbaru (default). */
    public function scopeSortPrice(Builder $query, ?string $dir): Builder
    {
        return match ($dir) {
            'asc' => $query->orderBy('price'),
            'desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };
    }

    /** Rata-rata rating dari ulasan yang sudah disetujui. */
    public function averageRating(): float
    {
        return round((float) $this->reviews()->where('is_approved', true)->avg('rating'), 1);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
