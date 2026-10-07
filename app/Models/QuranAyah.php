<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Verbatim Tanzil Quran text. Read-only: rows are only written by `php artisan quran:import`.
 *
 * @property int $index
 * @property int $sura
 * @property int $aya
 * @property string $text
 */
class QuranAyah extends Model
{
    protected $table = 'quran_text';

    protected $primaryKey = 'index';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'index' => 'integer',
            'sura' => 'integer',
            'aya' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        $deny = fn () => throw new LogicException('Quran text is read-only.');

        static::saving($deny);
        static::deleting($deny);
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(QuranSurah::class, 'sura');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sura')->orderBy('aya');
    }

    public function verseKey(): string
    {
        return "{$this->sura}:{$this->aya}";
    }
}
