<?php

namespace Database\Seeders;

use App\Models\QuranAyah;
use App\Models\RuqyahStep;
use Illuminate\Database\Seeder;

/**
 * Seeds the default Ruqyah steps once. The verses are copied verbatim from the
 * Tanzil text in quran_text (never typed by hand). Existing steps are never touched,
 * so content edited from the admin panel is preserved.
 */
class RuqyahStepsSeeder extends Seeder
{
    /**
     * @var array<int, array{title: string, instruction: string, sura: int, from: int, to: int, repeat: int}>
     */
    private const STEPS = [
        ['title' => 'سورة الفاتحة', 'instruction' => 'قراءة سورة الفاتحة 7 مرات', 'sura' => 1, 'from' => 1, 'to' => 7, 'repeat' => 7],
        ['title' => 'آية الكرسي', 'instruction' => 'قراءة آية الكرسي مرة واحدة', 'sura' => 2, 'from' => 255, 'to' => 255, 'repeat' => 1],
        ['title' => 'خواتيم سورة البقرة', 'instruction' => 'قراءة آخر آيتين من سورة البقرة مرة واحدة', 'sura' => 2, 'from' => 285, 'to' => 286, 'repeat' => 1],
        ['title' => 'آيات من سورة الأعراف', 'instruction' => 'قراءة الآيات من 117 إلى 122 من سورة الأعراف مرة واحدة', 'sura' => 7, 'from' => 117, 'to' => 122, 'repeat' => 1],
        ['title' => 'آيات من سورة يونس', 'instruction' => 'قراءة الآيات من 81 إلى 82 من سورة يونس مرة واحدة', 'sura' => 10, 'from' => 81, 'to' => 82, 'repeat' => 1],
        ['title' => 'آية من سورة طه', 'instruction' => 'قراءة الآية 69 من سورة طه مرة واحدة', 'sura' => 20, 'from' => 69, 'to' => 69, 'repeat' => 1],
        ['title' => 'سورة الإخلاص', 'instruction' => 'قراءة سورة الإخلاص 3 مرات', 'sura' => 112, 'from' => 1, 'to' => 4, 'repeat' => 3],
        ['title' => 'سورة الفلق', 'instruction' => 'قراءة سورة الفلق 3 مرات', 'sura' => 113, 'from' => 1, 'to' => 5, 'repeat' => 3],
        ['title' => 'سورة الناس', 'instruction' => 'قراءة سورة الناس 3 مرات', 'sura' => 114, 'from' => 1, 'to' => 6, 'repeat' => 3],
    ];

    public function run(): void
    {
        if (RuqyahStep::withTrashed()->exists()) {
            $this->command?->info('Ruqyah steps already exist; skipping.');

            return;
        }

        foreach (self::STEPS as $index => $step) {
            $ayat = QuranAyah::query()
                ->where('sura', $step['sura'])
                ->whereBetween('aya', [$step['from'], $step['to']])
                ->ordered()
                ->get(['sura', 'aya', 'text']);

            if ($ayat->count() !== $step['to'] - $step['from'] + 1) {
                $this->command?->warn("Quran text missing for {$step['title']}; run `php artisan quran:import` first. Skipped.");

                continue;
            }

            RuqyahStep::query()->create([
                'title' => $step['title'],
                'instruction' => $step['instruction'],
                'content' => $ayat
                    ->map(fn (QuranAyah $ayah): string => $ayah->text.' ﴿'.self::arabicDigits($ayah->aya).'﴾')
                    ->implode(' '),
                'repeat_count' => $step['repeat'],
                'status' => 'active',
                'sort_order' => $index + 1,
            ]);
        }
    }

    private static function arabicDigits(int $number): string
    {
        return strtr((string) $number, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);
    }
}
