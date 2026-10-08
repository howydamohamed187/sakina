<?php

namespace Database\Seeders;

use App\Models\DailyQuestion;
use App\Support\QuestionCategories;
use Illuminate\Database\Seeder;

/**
 * Basic, well-established facts only. The first option of every question is the correct
 * one; options are shuffled per user by the API.
 */
class DailyQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'body' => 'من أول الأنبياء؟',
                'category' => QuestionCategories::RELIGIOUS,
                'explanation' => 'آدم عليه السلام هو أبو البشر وأول الأنبياء.',
                'options' => ['آدم عليه السلام', 'نوح عليه السلام', 'إبراهيم عليه السلام', 'موسى عليه السلام'],
            ],
            [
                'body' => 'كم عدد أركان الإسلام؟',
                'category' => QuestionCategories::RELIGIOUS,
                'explanation' => 'أركان الإسلام خمسة: الشهادتان، وإقام الصلاة، وإيتاء الزكاة، وصوم رمضان، وحج البيت لمن استطاع إليه سبيلاً.',
                'options' => ['خمسة', 'أربعة', 'ستة', 'سبعة'],
            ],
            [
                'body' => 'كم عدد أركان الإيمان؟',
                'category' => QuestionCategories::RELIGIOUS,
                'explanation' => 'أركان الإيمان ستة: الإيمان بالله، وملائكته، وكتبه، ورسله، واليوم الآخر، والقدر خيره وشره.',
                'options' => ['ستة', 'خمسة', 'أربعة', 'سبعة'],
            ],
            [
                'body' => 'كم عدد الصلوات المفروضة في اليوم والليلة؟',
                'category' => QuestionCategories::RELIGIOUS,
                'explanation' => 'الصلوات المفروضة خمس: الفجر، والظهر، والعصر، والمغرب، والعشاء.',
                'options' => ['خمس صلوات', 'ثلاث صلوات', 'أربع صلوات', 'ست صلوات'],
            ],
            [
                'body' => 'ما هي أول سورة في المصحف؟',
                'category' => QuestionCategories::QURAN,
                'explanation' => 'سورة الفاتحة هي أول سورة في ترتيب المصحف، وتُسمّى أم الكتاب.',
                'options' => ['سورة الفاتحة', 'سورة البقرة', 'سورة العلق', 'سورة الناس'],
            ],
            [
                'body' => 'ما هي آخر سورة في المصحف؟',
                'category' => QuestionCategories::QURAN,
                'explanation' => 'سورة الناس هي آخر سورة في ترتيب المصحف، وهي السورة رقم 114.',
                'options' => ['سورة الناس', 'سورة الفلق', 'سورة الإخلاص', 'سورة الكوثر'],
            ],
            [
                'body' => 'كم عدد سور القرآن الكريم؟',
                'category' => QuestionCategories::QURAN,
                'explanation' => 'عدد سور القرآن الكريم 114 سورة، أولها الفاتحة وآخرها الناس.',
                'options' => ['114 سورة', '110 سور', '120 سورة', '99 سورة'],
            ],
            [
                'body' => 'ما أطول سورة في القرآن الكريم؟',
                'category' => QuestionCategories::QURAN,
                'explanation' => 'سورة البقرة أطول سور القرآن الكريم، وعدد آياتها 286 آية.',
                'options' => ['سورة البقرة', 'سورة آل عمران', 'سورة النساء', 'سورة المائدة'],
            ],
            [
                'body' => 'في أي سورة توجد آية الكرسي؟',
                'category' => QuestionCategories::QURAN,
                'explanation' => 'آية الكرسي هي الآية 255 من سورة البقرة.',
                'options' => ['سورة البقرة', 'سورة آل عمران', 'سورة يس', 'سورة الكهف'],
            ],
            [
                'body' => 'في أي شهر أُنزل القرآن الكريم؟',
                'category' => QuestionCategories::QURAN,
                'explanation' => 'قال تعالى: ﴿شَهْرُ رَمَضَانَ الَّذِي أُنزِلَ فِيهِ الْقُرْآنُ﴾ [البقرة: 185].',
                'options' => ['رمضان', 'شعبان', 'رجب', 'ذو الحجة'],
            ],
            [
                'body' => 'ما السورة التي تعدل ثلث القرآن؟',
                'category' => QuestionCategories::QURAN,
                'explanation' => 'أخبر النبي ﷺ أن سورة الإخلاص ﴿قُلْ هُوَ اللَّهُ أَحَدٌ﴾ تعدل ثلث القرآن، كما في صحيح البخاري.',
                'options' => ['سورة الإخلاص', 'سورة الفاتحة', 'سورة الكوثر', 'سورة الملك'],
            ],
            [
                'body' => 'ما اسم أم النبي محمد ﷺ؟',
                'category' => QuestionCategories::SEERAH,
                'explanation' => 'أم النبي ﷺ هي آمنة بنت وهب، وتوفيت وهو صغير.',
                'options' => ['آمنة بنت وهب', 'خديجة بنت خويلد', 'حليمة السعدية', 'فاطمة بنت أسد'],
            ],
            [
                'body' => 'في أي مدينة وُلد النبي محمد ﷺ؟',
                'category' => QuestionCategories::SEERAH,
                'explanation' => 'وُلد النبي ﷺ في مكة المكرمة في عام الفيل.',
                'options' => ['مكة المكرمة', 'المدينة المنورة', 'الطائف', 'القدس'],
            ],
            [
                'body' => 'إلى أي مدينة هاجر النبي ﷺ من مكة؟',
                'category' => QuestionCategories::SEERAH,
                'explanation' => 'هاجر النبي ﷺ من مكة إلى المدينة المنورة، وكانت تُسمّى يثرب، ومن الهجرة بدأ التاريخ الهجري.',
                'options' => ['المدينة المنورة', 'الطائف', 'الحبشة', 'القدس'],
            ],
            [
                'body' => 'من هو أول الخلفاء الراشدين؟',
                'category' => QuestionCategories::SEERAH,
                'explanation' => 'أبو بكر الصديق رضي الله عنه هو أول الخلفاء الراشدين بعد وفاة النبي ﷺ.',
                'options' => ['أبو بكر الصديق', 'عمر بن الخطاب', 'عثمان بن عفان', 'علي بن أبي طالب'],
            ],
            [
                'body' => 'ما القبلة الأولى للمسلمين؟',
                'category' => QuestionCategories::SEERAH,
                'explanation' => 'صلّى المسلمون أولاً إلى المسجد الأقصى في بيت المقدس، ثم تحولت القبلة إلى الكعبة المشرفة.',
                'options' => ['المسجد الأقصى', 'الكعبة المشرفة', 'المسجد النبوي', 'مسجد قباء'],
            ],
            [
                'body' => 'من النبي الذي التقمه الحوت؟',
                'category' => QuestionCategories::RELIGIOUS,
                'explanation' => 'التقم الحوت نبي الله يونس عليه السلام، فسبّح الله في بطنه فنجّاه، ويُلقّب بذي النون.',
                'options' => ['يونس عليه السلام', 'موسى عليه السلام', 'نوح عليه السلام', 'يوسف عليه السلام'],
            ],
            [
                'body' => 'من النبي الذي كلّمه الله تكليماً ولُقّب بكليم الله؟',
                'category' => QuestionCategories::RELIGIOUS,
                'explanation' => 'قال تعالى: ﴿وَكَلَّمَ اللَّهُ مُوسَىٰ تَكْلِيمًا﴾ [النساء: 164].',
                'options' => ['موسى عليه السلام', 'إبراهيم عليه السلام', 'عيسى عليه السلام', 'داود عليه السلام'],
            ],
        ];

        foreach ($questions as $index => $row) {
            $question = DailyQuestion::query()->withTrashed()->firstOrCreate(
                ['body' => $row['body']],
                [
                    'explanation' => $row['explanation'],
                    'category' => $row['category'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );

            if (blank($question->explanation)) {
                $question->update(['explanation' => $row['explanation']]);
            }

            if ($question->options()->exists()) {
                continue;
            }

            foreach ($row['options'] as $optionIndex => $body) {
                $question->options()->create([
                    'body' => $body,
                    'is_correct' => $optionIndex === 0,
                    'sort_order' => $optionIndex + 1,
                ]);
            }
        }
    }
}
