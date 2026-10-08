<?php

namespace App\Models;

use Database\Factories\RuqyahStepFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One step of the Ruqyah sequence. The step number shown to the customer is the
 * step's position among the active steps (ordered by sort_order), not a stored column.
 */
class RuqyahStep extends Model
{
    /** @use HasFactory<RuqyahStepFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'instruction',
        'content',
        'repeat_count',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'repeat_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
