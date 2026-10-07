<?php

namespace App\Models;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Audio URL templates support {surah} and {ayah} (zero-padded to 3 digits),
 * plus {surah_number} and {ayah_number} (unpadded).
 */
class QuranReciter extends Model
{
    protected $fillable = [
        'name',
        'image',
        'ayah_audio_url_template',
        'surah_audio_url_template',
        'is_default',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (QuranReciter $reciter): void {
            if ($reciter->is_default) {
                static::query()->whereKeyNot($reciter->getKey())->where('is_default', true)->update(['is_default' => false]);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_default')->orderBy('sort_order')->orderBy('id');
    }

    public function displayName(): string
    {
        return (string) Locales::pick($this->name);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Str::startsWith($this->image, ['http://', 'https://'])
            ? $this->image
            : Storage::disk('public')->url($this->image);
    }

    public function ayahAudioUrl(int $surah, int $ayah): ?string
    {
        return $this->fillTemplate($this->ayah_audio_url_template, $surah, $ayah);
    }

    public function surahAudioUrl(int $surah): ?string
    {
        return $this->fillTemplate($this->surah_audio_url_template, $surah);
    }

    private function fillTemplate(?string $template, int $surah, ?int $ayah = null): ?string
    {
        if (blank($template)) {
            return null;
        }

        return strtr($template, [
            '{surah}' => str_pad((string) $surah, 3, '0', STR_PAD_LEFT),
            '{ayah}' => str_pad((string) ($ayah ?? 0), 3, '0', STR_PAD_LEFT),
            '{surah_number}' => (string) $surah,
            '{ayah_number}' => (string) ($ayah ?? 0),
        ]);
    }
}
