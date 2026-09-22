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
        ];

        foreach ($adhkar as $index => $row) {
            Dhikr::query()->firstOrCreate(
                ['title' => $row['title']],
                [
                    'body' => $row['body'],
                    'category' => $row['category'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
