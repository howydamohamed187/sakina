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
        'description',
        'category',
        'is_countable',
        'target_count',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_countable' => 'boolean',
            'target_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Dhikr $dhikr): void {
            if (! $dhikr->is_countable) {
                $dhikr->target_count = null;
            }
        });
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(DhikrFavorite::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(DhikrProgress::class);
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

    public function scopeCountable(Builder $query): Builder
    {
        return $query->where('is_countable', true)->whereNotNull('target_count');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAvailableForTasbeeh(): bool
    {
        return $this->isActive() && $this->is_countable && $this->target_count > 0;
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
