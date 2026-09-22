<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DailyQuestion;
use App\Models\Dhikr;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_dashboard_shows_stats_charts_and_latest_records(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        Customer::factory()->create(['name' => 'سعيد محمود علي']);
        DailyQuestion::factory()->create(['body' => 'كم عدد أركان الإسلام؟']);
        Dhikr::factory()->create(['title' => 'أذكار الصباح المختارة']);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get('/admin')
            ->assertOk()
            ->assertSee('مرحباً بك في سكينة')
            ->assertSee('العملاء')
            ->assertSee('الأسئلة')
            ->assertSee('المشاركات')
            ->assertSee('العملاء الجدد')
            ->assertSee('مشاركات الأسئلة')
            ->assertSee('أحدث العملاء')
            ->assertSee('أحدث الأسئلة')
            ->assertSee('أحدث الأذكار')
            ->assertSee('سعيد محمود علي')
            ->assertSee('كم عدد أركان الإسلام؟')
            ->assertSee('أذكار الصباح المختارة');
    }
}
