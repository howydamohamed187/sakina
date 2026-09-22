<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\LocalizedNotification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $this->withHeader('X-Locale', 'ar')
            ->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('data.name', 'سكينة')
            ->assertJsonPath('data.status', 'ok');
    }

    public function test_health_respects_locale_header(): void
    {
        $this->withHeader('X-Locale', 'en')
            ->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('data.name', 'Sakina')
            ->assertJsonPath('message', 'Service is healthy.');
    }

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'user@sakina.test',
            'password' => 'password',
            'status' => 'active',
        ]);
        $user->assignRole(Role::findOrCreate('super_admin', 'web'));
        $user->refresh();

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/admin/auth/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'تم تسجيل الدخول بنجاح.')
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_profile_can_be_updated(): void
    {
        $user = User::factory()->create([
            'email' => 'profile@sakina.test',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/profile', [
                'name' => 'مستخدم سكينة',
                'email' => 'profile@sakina.test',
                'phone_country' => 'EG',
                'phone' => '01012345678',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'مستخدم سكينة')
            ->assertJsonPath('data.phone', '+201012345678')
            ->assertJsonPath('data.phone_country', 'EG')
            ->assertJsonPath('data.phone_national', '1012345678');
    }

    public function test_settings_are_public_and_follow_locale_header(): void
    {
        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.locale', 'ar')
            ->assertJsonPath('data.direction', 'rtl')
            ->assertJsonPath('data.site_name', 'سكينة')
            ->assertJsonPath('data.app_name', 'سكينة')
            ->assertJsonMissingPath('data.google_map_key');

        $this->withHeader('X-Locale', 'en')
            ->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.site_name', 'Sakina')
            ->assertJsonPath('data.app_name', 'Sakina');
    }

    public function test_settings_can_be_updated_and_localize_response(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Locale', 'en')
            ->putJson('/api/v1/settings', [
                'locale' => 'en',
                'theme' => 'dark',
            ])
            ->assertOk()
            ->assertJsonPath('data.locale', 'en')
            ->assertJsonPath('data.theme', 'dark')
            ->assertJsonPath('data.theme_label', 'Dark mode');
    }

    public function test_notifications_are_returned_in_requested_locale(): void
    {
        $user = User::factory()->create(['locale' => 'ar']);
        $user->notify(new LocalizedNotification('welcome'));

        $this->actingAs($user, 'sanctum')
            ->withHeader('X-Locale', 'en')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Welcome to Sakina');
    }
}
