<?php

namespace Database\Seeders;

use App\Models\Dua;
use App\Support\DuaCategories;
use Illuminate\Database\Seeder;

class DuasSeeder extends Seeder
{
    public function run(): void
    {
        $duas = [
            [
                'title' => 'دعاء الكرب',
                'category' => DuaCategories::DISTRESS,
                'body' => 'لا إله إلا الله العظيم الحليم، لا إله إلا الله رب العرش العظيم، لا إله إلا الله رب السماوات ورب الأرض ورب العرش الكريم.',
            ],
            [
                'title' => 'دعاء الهم والحزن',
                'category' => DuaCategories::DISTRESS,
                'body' => 'اللهم إني عبدك، ابن عبدك، ابن أمتك، ناصيتي بيدك، ماض فيّ حكمك، عدل فيّ قضاؤك.',
            ],
            [
                'title' => 'دعاء عام',
                'category' => DuaCategories::GENERAL,
                'body' => 'ربنا آتنا في الدنيا حسنة وفي الآخرة حسنة وقنا عذاب النار.',
            ],
            [
                'title' => 'دعاء السفر',
                'category' => DuaCategories::TRAVEL,
                'body' => 'سبحان الذي سخر لنا هذا وما كنا له مقرنين، وإنا إلى ربنا لمنقلبون.',
            ],
            [
                'title' => 'دعاء الهداية',
                'category' => DuaCategories::GUIDANCE,
                'body' => 'اللهم إني أسألك الهدى والتقى والعفاف والغنى.',
            ],
        ];

        foreach ($duas as $index => $row) {
            Dua::query()->firstOrCreate(
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
