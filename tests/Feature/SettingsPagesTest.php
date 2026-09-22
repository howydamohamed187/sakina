<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Settings\ManageDeveloper;
use App\Filament\Admin\Pages\Settings\ManageGeneral;
use App\Filament\Admin\Pages\Settings\ManageThirdParty;
use App\Models\Customer;
use App\Models\User;
use App\Services\OtpService;
use App\Settings\AppearanceSettings;
use App\Settings\DeveloperSettings;
use App\Settings\GeneralSettings;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    public function test_super_admin_can_update_general_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(ManageGeneral::class)
            ->fillForm([
                'app_name' => [
                    'ar' => 'سكينة التجريبية',
                    'en' => 'Sakina Demo',
                ],
                'app_email' => 'hello@sakina.test',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(GeneralSettings::class);

        $this->assertSame('سكينة التجريبية', $settings->app_name['ar']);
        $this->assertSame('Sakina Demo', $settings->app_name['en']);
        $this->assertSame('hello@sakina.test', $settings->app_email);
    }

    public function test_super_admin_can_save_social_links(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(ManageGeneral::class)
            ->fillForm([
                'app_name' => [
                    'ar' => 'سكينة',
                    'en' => 'Sakina',
                ],
                'social_links' => [
                    [
                        'icon' => 'fab fa-instagram',
                        'link' => 'https://instagram.com/sakina',
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(GeneralSettings::class);

        $this->assertSame('fab fa-instagram', $settings->social_links[0]['icon']);
        $this->assertSame('https://instagram.com/sakina', $settings->social_links[0]['link']);

        $this->get(ManageGeneral::getUrl())
            ->assertOk()
            ->assertSee('روابط التواصل');

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.social_links.0.icon', 'fab fa-instagram')
            ->assertJsonPath('data.social_links.0.url', 'https://instagram.com/sakina');
    }

    public function test_appearance_fonts_apply_to_panel_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(ManageGeneral::class)
            ->fillForm([
                'app_name' => [
                    'ar' => 'سكينة',
                    'en' => 'Sakina',
                ],
                'font_family' => 'Tajawal',
                'font_size' => 'lg',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $appearance = app(AppearanceSettings::class);

        $this->assertSame('Tajawal', $appearance->font_family);
        $this->assertSame('lg', $appearance->font_size);

        $this->actingAs($admin, 'admin')
            ->get(ManageGeneral::getUrl())
            ->assertOk()
            ->assertSee('Tajawal', false)
            ->assertSee('18px', false)
            ->assertSee('العربية')
            ->assertSee('English')
            ->assertSee('سكينة')
            ->assertDontSee('[object Object]');
    }

    public function test_admin_can_open_general_and_third_party_but_not_developer(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);

        $this->actingAs($admin, 'admin')
            ->get(ManageGeneral::getUrl())
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(ManageThirdParty::getUrl())
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(ManageDeveloper::getUrl())
            ->assertForbidden();
    }

    public function test_static_otp_is_used_when_random_is_disabled(): void
    {
        $developer = app(DeveloperSettings::class);
        $developer->otp_code_is_random = false;
        $developer->save();

        Customer::factory()->create([
            'email' => 'otp@sakina.test',
        ]);

        $code = app(OtpService::class)->send('otp@sakina.test');

        $this->assertSame('1234', $code);
    }

    public function test_super_admin_can_open_developer_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(ManageDeveloper::class)
            ->fillForm([
                'debug_mode' => true,
                'otp_code_is_random' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $developer = app(DeveloperSettings::class);

        $this->assertTrue($developer->debug_mode);
        $this->assertFalse($developer->otp_code_is_random);
    }

    public function test_public_app_config_exposes_general_settings_without_secrets(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->app_name = [
            'ar' => 'سكينة التجريبية',
            'en' => 'Sakina Demo',
        ];
        $settings->save();

        $this->withHeader('X-Locale', 'ar')
            ->getJson('/api/v1/app-config')
            ->assertOk()
            ->assertJsonPath('data.app_name', 'سكينة التجريبية')
            ->assertJsonPath('data.app_names.en', 'Sakina Demo')
            ->assertJsonPath('data.font_family', 'Alexandria')
            ->assertJsonMissingPath('data.google_map_key')
            ->assertJsonMissingPath('data.tranportal_password');

        $this->withHeader('X-Locale', 'en')
            ->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.site_name', 'Sakina Demo')
            ->assertJsonPath('data.app_name', 'Sakina Demo');
    }
}
