<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyQuestionOption extends Model
{
    protected $fillable = [
        'daily_question_id',
        'body',
        'is_correct',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(DailyQuestion::class, 'daily_question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(DailyQuestionAnswer::class);
    }
}
