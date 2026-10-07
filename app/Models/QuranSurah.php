<?php

namespace App\Models;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name_ar
 * @property string $name_en
 * @property ?string $name_en_translation
 * @property string $revelation_type
 * @property int $ayahs_count
 * @property ?array $information
 */
class QuranSurah extends Model
{
    public const MECCAN = 'meccan';

    public const MEDINAN = 'medinan';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name_ar',
        'name_en',
        'name_en_translation',
        'revelation_type',
        'ayahs_count',
        'information',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ayahs_count' => 'integer',
            'information' => 'array',
        ];
    }

    public function ayahs(): HasMany
    {
        return $this->hasMany(QuranAyah::class, 'sura')->orderBy('aya');
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return __('api.quran.surah_name', [
            'name' => $locale === 'en' ? $this->name_en : $this->name_ar,
        ], $locale);
    }

    public function revelationTypeLabel(): string
    {
        return __("api.quran.revelation_types.{$this->revelation_type}");
    }

    public function informationText(): ?string
    {
        $text = Locales::pick($this->information);

        return filled($text) ? (string) $text : null;
    }
}
