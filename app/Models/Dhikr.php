<?php

namespace App\Models;

use App\Support\DhikrCategories;
use Database\Factories\DhikrFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dhikr extends Model
{
    /** @use HasFactory<DhikrFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'category',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(DhikrFavorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'dhikr_favorites')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function favoritesCount(): int
    {
        return (int) ($this->favorites_count ?? $this->favorites()->count());
    }

    public function categoryLabel(): string
    {
        return DhikrCategories::label($this->category);
    }

    public function isFavoritedBy(?Customer $customer): bool
    {
        if (! $customer) {
            return false;
        }

        if ($this->relationLoaded('favorites')) {
            return $this->favorites->contains('customer_id', $customer->id);
        }

        return $this->favorites()->where('customer_id', $customer->id)->exists();
    }
}
