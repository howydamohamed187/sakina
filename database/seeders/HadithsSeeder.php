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
                'narrator' => 'عمر بن الخطاب رضي الله عنه',
                'source' => 'متفق عليه (صحيح البخاري وصحيح مسلم)',
                'explanation' => 'صلاح العمل وقبوله مرتبط بالنية، فيُجازى كل إنسان على ما قصده بعمله.',
            ],
            [
                'title' => 'حديث عن الصوم',
                'body' => 'من صام رمضان إيمانًا واحتسابًا غفر له ما تقدم من ذنبه.',
                'narrator' => 'أبو هريرة رضي الله عنه',
                'source' => 'متفق عليه (صحيح البخاري وصحيح مسلم)',
                'explanation' => 'من صام رمضان مؤمنًا بفرضيته طالبًا الأجر من الله غُفر له ما سبق من ذنوبه.',
            ],
            [
                'title' => 'حديث عن الدين',
                'body' => 'الدين النصيحة. قلنا: لمن؟ قال: لله، ولكتابه، ولرسوله، ولأئمة المسلمين وعامتهم.',
                'narrator' => 'تميم الداري رضي الله عنه',
                'source' => 'صحيح مسلم',
                'explanation' => 'النصيحة عماد الدين، وتكون بالإخلاص لله وكتابه ورسوله، وبإرادة الخير لولاة الأمر وعامة المسلمين.',
            ],
            [
                'title' => 'حديث عن الإيمان',
                'body' => 'لا يؤمن أحدكم حتى يحب لأخيه ما يحب لنفسه.',
                'narrator' => 'أنس بن مالك رضي الله عنه',
                'source' => 'متفق عليه (صحيح البخاري وصحيح مسلم)',
                'explanation' => 'كمال الإيمان أن يحب المسلم لإخوانه من الخير ما يحبه لنفسه.',
            ],
            [
                'title' => 'حديث عن الرفق',
                'body' => 'إن الرفق لا يكون في شيء إلا زانه، ولا ينزع من شيء إلا شانه.',
                'narrator' => 'عائشة رضي الله عنها',
                'source' => 'صحيح مسلم',
                'explanation' => 'اللين والرفق يجمّلان الأقوال والأفعال، وغيابهما يعيبها.',
            ],
        ];

        foreach ($hadiths as $index => $row) {
            $hadith = Hadith::query()->withTrashed()->firstOrCreate(
                ['title' => $row['title']],
                [
                    'body' => $row['body'],
                    'narrator' => $row['narrator'],
                    'source' => $row['source'],
                    'explanation' => $row['explanation'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );

            $missing = collect(['narrator', 'source', 'explanation'])
                ->filter(fn (string $field): bool => blank($hadith->{$field}))
                ->mapWithKeys(fn (string $field): array => [$field => $row[$field]])
                ->all();

            if ($missing !== []) {
                $hadith->update($missing);
            }
        }
    }
}
