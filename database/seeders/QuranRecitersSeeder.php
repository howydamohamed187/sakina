<?php

namespace Database\Seeders;

use App\Models\QuranReciter;
use Illuminate\Database\Seeder;

class QuranRecitersSeeder extends Seeder
{
    public function run(): void
    {
        $reciters = [
            [
                'name' => [
                    'ar' => 'الشيخ عبد الباسط عبد الصمد',
                    'en' => 'Sheikh Abdul Basit Abdul Samad',
                    'ckb' => 'شێخ عەبدولباست عەبدولسەمەد',
                ],
                'ayah_audio_url_template' => 'https://everyayah.com/data/Abdul_Basit_Murattal_192kbps/{surah}{ayah}.mp3',
                'surah_audio_url_template' => 'https://cdn.mp3quran.net/audio/abdulbasit-abdulsamad/r3/{surah}.mp3',
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => [
                    'ar' => 'الشيخ مشاري راشد العفاسي',
                    'en' => 'Sheikh Mishary Rashid Alafasy',
                    'ckb' => 'شێخ میشاری ڕاشد ئەلعەفاسی',
                ],
                'ayah_audio_url_template' => 'https://everyayah.com/data/Alafasy_128kbps/{surah}{ayah}.mp3',
                'surah_audio_url_template' => 'https://cdn.mp3quran.net/audio/mishary-alafasy/r1/{surah}.mp3',
                'is_default' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($reciters as $reciter) {
            QuranReciter::query()->updateOrCreate(
                ['ayah_audio_url_template' => $reciter['ayah_audio_url_template']],
                $reciter + ['status' => 'active']
            );
        }
    }
}
