<?php

namespace Database\Seeders;

use App\Models\Hadith;
use Illuminate\Database\Seeder;

class HadithsSeeder extends Seeder
{
    public function run(): void
    {
        $hadiths = [
            [
                'title' => 'حديث عن الأعمال',
                'body' => 'إنما الأعمال بالنيات، وإنما لكل امرئ ما نوى، فمن كانت هجرته إلى الله ورسوله فهجرته إلى الله ورسوله، ومن كانت هجرته لدنيا يصيبها أو امرأة ينكحها فهجرته إلى ما هاجر إليه.',
            ],
            [
                'title' => 'حديث عن الصوم',
                'body' => 'من صام رمضان إيمانًا واحتسابًا غفر له ما تقدم من ذنبه.',
            ],
            [
                'title' => 'حديث عن الدين',
                'body' => 'الدين النصيحة. قلنا: لمن؟ قال: لله، ولكتابه، ولرسوله، ولأئمة المسلمين وعامتهم.',
            ],
            [
                'title' => 'حديث عن الإيمان',
                'body' => 'لا يؤمن أحدكم حتى يحب لأخيه ما يحب لنفسه.',
            ],
            [
                'title' => 'حديث عن الرفق',
                'body' => 'إن الرفق لا يكون في شيء إلا زانه، ولا ينزع من شيء إلا شانه.',
            ],
        ];

        foreach ($hadiths as $index => $row) {
            Hadith::query()->firstOrCreate(
                ['title' => $row['title']],
                [
                    'body' => $row['body'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
