<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\DailyQuestion;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyQuestionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Roles::ensure(Roles::CUSTOMER);
    }

    public function test_customer_gets_one_random_question_per_day(): void
    {
        DailyQuestion::factory()->withOptions()->create(['body' => 'سؤال أول']);
        DailyQuestion::factory()->withOptions([
            ['body' => 'أ', 'is_correct' => true],
            ['body' => 'ب', 'is_correct' => false],
        ])->create(['body' => 'سؤال ثان']);

        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $firstResponse = $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->getJson('/api/v1/daily-question')
            ->assertOk()
            ->assertJsonPath('message', 'سؤال اليوم جاهز.')
            ->json('data');

        $this->assertContains($firstResponse['body'], ['سؤال أول', 'سؤال ثان']);
        $this->assertGreaterThanOrEqual(2, count($firstResponse['answers']));
        $this->assertSame(0, $firstResponse['answered']);
        $this->assertArrayNotHasKey('is_correct', $firstResponse['answers'][0]);

        $second = $this->withToken($token)
            ->getJson('/api/v1/daily-question')
            ->assertOk()
            ->json('data');

        $this->assertSame($firstResponse['id'], $second['id']);
        $this->assertSame($firstResponse['shown_on'], $second['shown_on']);
    }

    public function test_answers_are_shuffled_and_hidden_until_submit(): void
    {
        $question = DailyQuestion::factory()->withOptions()->create();
        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $data = $this->withToken($token)
            ->getJson('/api/v1/daily-question')
            ->assertOk()
            ->json('data');

        $this->assertSame($question->id, $data['id']);
        $this->assertCount(4, $data['answers']);
        $this->assertEqualsCanonicalizing(
            $question->options->pluck('id')->all(),
            collect($data['answers'])->pluck('id')->all()
        );
        $this->assertArrayNotHasKey('is_correct', $data['answers'][0]);
    }

    public function test_customer_can_answer_once_and_see_result(): void
    {
        $question = DailyQuestion::factory()->withOptions()->create();
        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;
        $correctId = $question->correctOption()?->id;
        $wrongId = $question->options->firstWhere('is_correct', false)?->id;

        $this->withToken($token)->getJson('/api/v1/daily-question')->assertOk();

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/daily-question/answer', [
                'option_id' => $wrongId,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'إجابة خاطئة.')
            ->assertJsonPath('data.answered', 1)
            ->assertJsonPath('data.is_correct', 0)
            ->assertJsonPath('data.selected_option_id', $wrongId);

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/daily-question/answer', [
                'option_id' => $correctId,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'سبق أن أجبت على سؤال اليوم.');
    }

    public function test_questions_do_not_repeat_until_cycle_ends(): void
    {
        $questions = collect([
            DailyQuestion::factory()->withOptions()->create(['body' => 'س1']),
            DailyQuestion::factory()->withOptions()->create(['body' => 'س2']),
            DailyQuestion::factory()->withOptions()->create(['body' => 'س3']),
        ]);

        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;
        $seen = [];

        foreach ([0, 1, 2] as $day) {
            Carbon::setTestNow(now()->addDays($day === 0 ? 0 : 1));

            $id = $this->withToken($token)
                ->getJson('/api/v1/daily-question')
                ->assertOk()
                ->json('data.id');

            $seen[] = $id;
        }

        $this->assertCount(3, array_unique($seen));
        $this->assertEqualsCanonicalizing($questions->pluck('id')->all(), $seen);

        Carbon::setTestNow(now()->addDay());

        $fourth = $this->withToken($token)
            ->getJson('/api/v1/daily-question')
            ->assertOk()
            ->json('data.id');

        $this->assertContains($fourth, $questions->pluck('id')->all());
    }

    public function test_inactive_questions_are_not_assigned(): void
    {
        DailyQuestion::factory()->inactive()->withOptions()->create(['body' => 'مخفي']);
        $active = DailyQuestion::factory()->withOptions()->create(['body' => 'ظاهر']);
        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/daily-question')
            ->assertOk()
            ->assertJsonPath('data.id', $active->id)
            ->assertJsonPath('data.body', 'ظاهر');
    }

    public function test_guest_cannot_fetch_daily_question(): void
    {
        $this->getJson('/api/v1/daily-question')->assertUnauthorized();
    }
}
