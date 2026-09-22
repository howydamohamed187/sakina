<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyQuestionAnswer extends Model
{
    protected $fillable = [
        'daily_question_id',
        'daily_question_option_id',
        'customer_id',
        'daily_question_assignment_id',
        'option_body',
        'is_correct',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(DailyQuestion::class, 'daily_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(DailyQuestionOption::class, 'daily_question_option_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DailyQuestionAssignment::class, 'daily_question_assignment_id');
    }

    public function resultLabel(): string
    {
        return $this->is_correct
            ? __('app.daily_question_result.correct')
            : __('app.daily_question_result.wrong');
    }
}
