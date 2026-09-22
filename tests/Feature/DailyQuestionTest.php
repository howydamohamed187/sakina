<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\DailyQuestionResource\Pages\CreateDailyQuestion;
use App\Filament\Admin\Resources\DailyQuestionResource\Pages\ListDailyQuestions;
use App\Filament\Admin\Resources\DailyQuestionResource\Pages\ViewDailyQuestion;
use App\Models\Customer;
use App\Models\DailyQuestion;
use App\Models\DailyQuestionAnswer;
use App\Models\DailyQuestionAssignment;
use App\Models\User;
use App\Support\QuestionCategories;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class DailyQuestionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_questions_table_is_visible(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        DailyQuestion::factory()->withOptions()->create([
            'body' => 'من أول الأنبياء؟',
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ListDailyQuestions::getUrl())
            ->assertOk()
            ->assertSee('من أول الأنبياء؟')
            ->assertSee('آدم عليه السلام')
            ->assertSee('عدد المشاركات');
    }

    public function test_admin_can_create_daily_question(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateDailyQuestion::class)
            ->fillForm([
                'body' => 'كم عدد أركان الإسلام؟',
                'category' => QuestionCategories::RELIGIOUS,
                'status' => true,
                'options' => [
                    ['body' => 'أربعة', 'is_correct' => false],
                    ['body' => 'خمسة', 'is_correct' => true],
                    ['body' => 'ستة', 'is_correct' => false],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $question = DailyQuestion::query()->where('body', 'كم عدد أركان الإسلام؟')->first();

        $this->assertNotNull($question);
        $this->assertSame('خمسة', $question->correctOption()?->body);
        $this->assertSame(3, $question->options()->count());
    }

    public function test_question_details_show_stats_and_answers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $question = DailyQuestion::factory()->withOptions()->create([
            'body' => 'من أول الأنبياء؟',
        ]);
        $customer = Customer::factory()->create(['name' => 'أحمد علي حسن']);
        $assignment = DailyQuestionAssignment::query()->create([
            'daily_question_id' => $question->id,
            'customer_id' => $customer->id,
            'shown_on' => now()->toDateString(),
            'cycle' => 1,
        ]);
        DailyQuestionAnswer::query()->create([
            'daily_question_id' => $question->id,
            'daily_question_option_id' => $question->correctOption()?->id,
            'customer_id' => $customer->id,
            'daily_question_assignment_id' => $assignment->id,
            'option_body' => 'آدم عليه السلام',
            'is_correct' => true,
            'answered_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ViewDailyQuestion::getUrl(['record' => $question]))
            ->assertOk()
            ->assertSee('من أول الأنبياء؟')
            ->assertSee('عدد المشاركات')
            ->assertSee('عدد الإجابات الصحيحة')
            ->assertSee('آدم عليه السلام');

        $this->assertDatabaseHas('daily_question_answers', [
            'daily_question_id' => $question->id,
            'customer_id' => $customer->id,
            'is_correct' => 1,
        ]);
    }

    public function test_admin_can_deactivate_question(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);
        $question = DailyQuestion::factory()->withOptions()->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(ListDailyQuestions::class)
            ->callTableAction('toggleStatus', $question);

        $this->assertSame('suspended', $question->fresh()->status);
    }
}
