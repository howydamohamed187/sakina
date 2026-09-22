<?php

namespace App\Models;

use App\Support\QuestionCategories;
use Database\Factories\DailyQuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyQuestion extends Model
{
    /** @use HasFactory<DailyQuestionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
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

    public function options(): HasMany
    {
        return $this->hasMany(DailyQuestionOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DailyQuestionAssignment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(DailyQuestionAnswer::class);
    }

    public function correctOption(): ?DailyQuestionOption
    {
        $this->loadMissing('options');

        return $this->options->firstWhere('is_correct', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function impressionsCount(): int
    {
        return (int) ($this->assignments_count ?? $this->assignments()->count());
    }

    public function participationsCount(): int
    {
        return (int) ($this->answers_count ?? $this->answers()->count());
    }

    public function correctAnswersCount(): int
    {
        return (int) ($this->correct_answers_count ?? $this->answers()->where('is_correct', true)->count());
    }

    public function wrongAnswersCount(): int
    {
        return max(0, $this->participationsCount() - $this->correctAnswersCount());
    }

    public function successRate(): ?float
    {
        $total = $this->participationsCount();

        if ($total === 0) {
            return null;
        }

        return round(($this->correctAnswersCount() / $total) * 100, 1);
    }

    public function successRateLabel(): string
    {
        $rate = $this->successRate();

        return $rate === null ? '—' : $rate.'%';
    }

    public function categoryLabel(): string
    {
        return QuestionCategories::label($this->category);
    }

    protected function correctAnswer(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->correctOption()?->body);
    }
}
