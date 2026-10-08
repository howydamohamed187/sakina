<?php

namespace App\Support;

use App\Models\Dhikr;
use App\Models\Dua;
use App\Models\Hadith;
use Illuminate\Database\Eloquent\Model;

/**
 * Content types a customer can save to favorites. Each model keeps its own favorites
 * table (hadith_favorites, dhikr_favorites, dua_favorites) unique per customer.
 */
class FavoriteTypes
{
    public const HADITH = 'hadith';

    public const DHIKR = 'dhikr';

    public const DUA = 'dua';

    /** @var array<string, array{model: class-string<Model>, added: string, removed: string}> */
    private const TYPES = [
        self::HADITH => ['model' => Hadith::class, 'added' => 'api.hadith_favorited', 'removed' => 'api.hadith_unfavorited'],
        self::DHIKR => ['model' => Dhikr::class, 'added' => 'api.dhikr_favorited', 'removed' => 'api.dhikr_unfavorited'],
        self::DUA => ['model' => Dua::class, 'added' => 'api.dua_favorited', 'removed' => 'api.dua_unfavorited'],
    ];

    private const ALIASES = [
        'hadiths' => self::HADITH,
        'adhkar' => self::DHIKR,
        'adhkars' => self::DHIKR,
        'dhikrs' => self::DHIKR,
        'duas' => self::DUA,
    ];

    /**
     * @return list<string> accepted values, including plural aliases
     */
    public static function accepted(): array
    {
        return [...array_keys(self::TYPES), ...array_keys(self::ALIASES)];
    }

    public static function normalize(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $type = strtolower(trim($type));

        return isset(self::TYPES[$type]) ? $type : (self::ALIASES[$type] ?? null);
    }

    /**
     * @return class-string<Model>
     */
    public static function model(string $type): string
    {
        return self::TYPES[$type]['model'];
    }

    public static function message(string $type, bool $added): string
    {
        return __(self::TYPES[$type][$added ? 'added' : 'removed']);
    }
}
