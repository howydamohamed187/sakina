<?php

namespace Database\Seeders;

use App\Models\Dhikr;
use App\Support\DhikrCategories;
use Illuminate\Database\Seeder;

class AdhkarSeeder extends Seeder
{
    public function run(): void
    {
        $adhkar = [
            [
                'title' => 'سيد الاستغفار',
                'category' => DhikrCategories::MORNING,
                'body' => 'اللهم أنت ربي لا إله إلا أنت، خلقتني وأنا عبدك، وأنا على عهدك ووعدك ما استطعت، أعوذ بك من شر ما صنعت، أبوء لك بنعمتك عليّ، وأبوء بذنبي، فاغفر لي، فإنه لا يغفر الذنوب إلا أنت.',
            ],
            [
                'title' => 'أصبحنا وأصبح الملك لله',
                'category' => DhikrCategories::MORNING,
                'body' => 'أصبحنا وأصبح الملك لله، والحمد لله، لا إله إلا الله وحده لا شريك له، له الملك وله الحمد وهو على كل شيء قدير.',
            ],
            [
                'title' => 'أمسينا وأمسى الملك لله',
                'category' => DhikrCategories::EVENING,
                'body' => 'أمسينا وأمسى الملك لله، والحمد لله، لا إله إلا الله وحده لا شريك له، له الملك وله الحمد وهو على كل شيء قدير.',
            ],
            [
                'title' => 'دعاء النوم',
                'category' => DhikrCategories::SLEEP,
                'body' => 'باسمك اللهم أموت وأحيا.',
            ],
            [
                'title' => 'دعاء الاستفتاح',
                'category' => DhikrCategories::PRAYER,
                'body' => 'اللهم باعد بيني وبين خطاياي كما باعدت بين المشرق والمغرب.',
            ],
            [
                'title' => 'سبحان الله',
                'category' => DhikrCategories::PRAYER,
                'body' => 'سبحان الله',
                'description' => 'من التسبيح بعد كل صلاة: ثلاثًا وثلاثين مرة، ومن قالها مع التحميد والتكبير غُفرت خطاياه وإن كانت مثل زبد البحر.',
                'target_count' => 33,
            ],
            [
                'title' => 'الحمد لله',
                'category' => DhikrCategories::PRAYER,
                'body' => 'الحمد لله',
                'description' => 'الحمد لله تملأ الميزان، وتُقال بعد كل صلاة ثلاثًا وثلاثين مرة.',
                'target_count' => 33,
            ],
            [
                'title' => 'الله أكبر',
                'category' => DhikrCategories::PRAYER,
                'body' => 'الله أكبر',
                'description' => 'من التسبيح بعد كل صلاة: ثلاثًا وثلاثين مرة، ويُختم المئة بـ: لا إله إلا الله وحده لا شريك له.',
                'target_count' => 33,
            ],
            [
                'title' => 'سبحان الله وبحمده',
                'category' => DhikrCategories::GENERAL,
                'body' => 'سبحان الله وبحمده سبحان الله العظيم',
                'description' => 'كلمتان خفيفتان على اللسان، ثقيلتان في الميزان، حبيبتان إلى الرحمن.',
                'target_count' => 100,
            ],
            [
                'title' => 'لا إله إلا الله',
                'category' => DhikrCategories::GENERAL,
                'body' => 'لا إله إلا الله',
                'description' => 'أفضل الذكر لا إله إلا الله، وهي كلمة التوحيد.',
                'target_count' => 100,
            ],
            [
                'title' => 'أستغفر الله',
                'category' => DhikrCategories::GENERAL,
                'body' => 'أستغفر الله',
                'description' => 'كان النبي ﷺ يستغفر الله ويتوب إليه في اليوم أكثر من سبعين مرة.',
                'target_count' => 100,
            ],
        ];

        foreach ($adhkar as $index => $row) {
            $dhikr = Dhikr::query()->firstOrCreate(
                ['title' => $row['title']],
                [
                    'body' => $row['body'],
                    'description' => $row['description'] ?? null,
                    'category' => $row['category'],
                    'is_countable' => isset($row['target_count']),
                    'target_count' => $row['target_count'] ?? null,
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );

            if (blank($dhikr->description) && filled($row['description'] ?? null)) {
                $dhikr->update(['description' => $row['description']]);
            }
        }
    }
}
