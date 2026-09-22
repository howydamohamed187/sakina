<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyQuestionAssignment extends Model
{
    protected $fillable = [
        'daily_question_id',
        'customer_id',
        'shown_on',
        'cycle',
    ];

    protected function casts(): array
    {
        return [
            'shown_on' => 'date',
            'cycle' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(DailyQuestion::class, 'daily_question_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function answer(): HasOne
    {
        return $this->hasOne(DailyQuestionAnswer::class);
    }
}
