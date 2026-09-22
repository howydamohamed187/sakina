<?php

namespace App\Models;

use App\Support\DuaCategories;
use Database\Factories\DuaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dua extends Model
{
    /** @use HasFactory<DuaFactory> */
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
        return $this->hasMany(DuaFavorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'dua_favorites')
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
        return DuaCategories::label($this->category);
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
