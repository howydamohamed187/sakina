<?php

namespace Database\Seeders;

use App\Models\DailyQuestion;
use App\Support\QuestionCategories;
use Illuminate\Database\Seeder;

class DailyQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'body' => 'من أول الأنبياء؟',
                'category' => QuestionCategories::RELIGIOUS,
                'options' => [
                    ['body' => 'آدم عليه السلام', 'is_correct' => true],
                    ['body' => 'نوح عليه السلام', 'is_correct' => false],
                    ['body' => 'إبراهيم عليه السلام', 'is_correct' => false],
                    ['body' => 'موسى عليه السلام', 'is_correct' => false],
                ],
            ],
            [
                'body' => 'كم عدد أركان الإسلام؟',
                'category' => QuestionCategories::RELIGIOUS,
                'options' => [
                    ['body' => 'أربعة', 'is_correct' => false],
                    ['body' => 'خمسة', 'is_correct' => true],
                    ['body' => 'ستة', 'is_correct' => false],
                    ['body' => 'سبعة', 'is_correct' => false],
                ],
            ],
            [
                'body' => 'ما هي أول سورة في المصحف؟',
                'category' => QuestionCategories::QURAN,
                'options' => [
                    ['body' => 'سورة البقرة', 'is_correct' => false],
                    ['body' => 'سورة العلق', 'is_correct' => false],
                    ['body' => 'سورة الفاتحة', 'is_correct' => true],
                    ['body' => 'سورة الناس', 'is_correct' => false],
                ],
            ],
            [
                'body' => 'في أي شهر فُرض صيام رمضان؟',
                'category' => QuestionCategories::RELIGIOUS,
                'options' => [
                    ['body' => 'شعبان', 'is_correct' => false],
                    ['body' => 'رمضان', 'is_correct' => true],
                    ['body' => 'شوال', 'is_correct' => false],
                    ['body' => 'رجب', 'is_correct' => false],
                ],
            ],
            [
                'body' => 'ما اسم أم النبي محمد ﷺ؟',
                'category' => QuestionCategories::SEERAH,
                'options' => [
                    ['body' => 'خديجة بنت خويلد', 'is_correct' => false],
                    ['body' => 'آمنة بنت وهب', 'is_correct' => true],
                    ['body' => 'حليمة السعدية', 'is_correct' => false],
                    ['body' => 'فاطمة بنت أسد', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $index => $row) {
            $question = DailyQuestion::query()->firstOrCreate(
                ['body' => $row['body']],
                [
                    'category' => $row['category'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );

            if ($question->options()->exists()) {
                continue;
            }

            foreach ($row['options'] as $optionIndex => $option) {
                $question->options()->create([
                    'body' => $option['body'],
                    'is_correct' => $option['is_correct'],
                    'sort_order' => $optionIndex + 1,
                ]);
            }
        }
    }
}
