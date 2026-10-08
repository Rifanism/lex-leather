<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const MATERIALS = ['genuine', 'synthetic'];

    /** Sentinel stored when no photo has been uploaded yet. */
    public const PLACEHOLDER_IMAGE = 'products/placeholder.svg';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'material',
        'price',
        'stock',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Single source of truth for the customer-facing material name. Views must
     * never spell this out themselves.
     *
     * @return array<string, string>
     */
    public static function materialLabels(): array
    {
        return [
            'genuine' => 'Kulit Asli',
            'synthetic' => 'Kulit Sintetis',
        ];
    }

    public function materialLabel(): string
    {
        return static::materialLabels()[$this->material] ?? $this->material;
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Only products a customer is allowed to see and buy. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }))
            ->when($filters['category'] ?? null, fn (Builder $q, $slug) => $q->whereHas(
                'category',
                fn (Builder $c) => $c->where('slug', $slug)
            ))
            ->when($filters['material'] ?? null, fn (Builder $q, $material) => $q->where('material', $material))
            // `when()` hands the condition value to the callback, so the guard
            // must forward the bound itself, not `isset()`.
            ->when(($filters['min_price'] ?? null) !== null, fn (Builder $q) => $q->where('price', '>=', (int) $filters['min_price']))
            ->when(($filters['max_price'] ?? null) !== null, fn (Builder $q) => $q->where('price', '<=', (int) $filters['max_price']));
    }

    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Public URL for the uploaded photo, served through the storage symlink. */
    public function imageUrl(): string
    {
        return $this->hasImage()
            ? asset('storage/'.$this->image)
            : asset('images/product-placeholder.svg');
    }

    /** Products always carry an `image` value, so the placeholder is the sentinel. */
    public function hasImage(): bool
    {
        return filled($this->image) && $this->image !== self::PLACEHOLDER_IMAGE;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
