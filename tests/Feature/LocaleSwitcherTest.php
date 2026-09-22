<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Tests\TestCase;

class LocaleSwitcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_admin_can_switch_panel_locale_to_english(): void
    {
        $admin = User::factory()->create(['locale' => 'ar']);
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin')
            ->from('/admin')
            ->get(route('locale.switch', ['locale' => 'en']))
            ->assertRedirect('/admin');

        $this->assertSame('en', session('locale'));
        $this->assertSame('en', $admin->fresh()->locale);

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Welcome to Sakina')
            ->assertSee('Dashboard')
            ->assertSee('Home')
            ->assertDontSee('مرحباً بك في سكينة');
    }

    public function test_admin_can_switch_panel_locale_to_kurdish(): void
    {
        $admin = User::factory()->create(['locale' => 'ar']);
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin')
            ->from('/admin')
            ->get(route('locale.switch', ['locale' => 'ckb']))
            ->assertRedirect('/admin');

        $this->assertSame('ckb', session('locale'));
        $this->assertSame('ckb', $admin->fresh()->locale);

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('بەخێربێیت بۆ سکینە')
            ->assertSee('داشبۆرد')
            ->assertDontSee('مرحباً بك في سكينة');
    }
}
