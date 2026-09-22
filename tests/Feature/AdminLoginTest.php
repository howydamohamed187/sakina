<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use App\Filament\Pages\Auth\PasswordReset\ResetPassword;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
    }

    public function test_guest_is_redirected_from_admin_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_login_and_see_welcome_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@sakina.test',
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get('/admin')
            ->assertOk()
            ->assertSee('مرحباً بك في سكينة');
    }

    public function test_admin_can_submit_login_form(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@sakina.test',
        ]);
        $admin->assignRole('super_admin');

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'admin@sakina.test',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_login_page_defaults_to_arabic_without_locale_select(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('تسجيل الدخول')
            ->assertSee('نسيت كلمة المرور؟')
            ->assertDontSee('locale-switcher', false)
            ->assertDontSee('كود الدولة');
    }

    public function test_forgot_password_page_is_available(): void
    {
        $this->get('/admin/password-reset/request')
            ->assertOk()
            ->assertSee('نسيت كلمة المرور؟')
            ->assertSee('أرسل البريد الإلكتروني');
    }

    public function test_admin_can_request_password_reset_email(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'email' => 'reset@sakina.test',
        ]);
        $admin->assignRole('super_admin');

        Livewire::test(RequestPasswordReset::class)
            ->fillForm([
                'email' => 'reset@sakina.test',
            ])
            ->call('request')
            ->assertNotified();

        Notification::assertSentTo($admin, ResetPasswordNotification::class);
    }

    public function test_password_reset_is_not_sent_without_staff_role(): void
    {
        Notification::fake();

        User::factory()->create([
            'email' => 'guest-reset@sakina.test',
        ]);

        Livewire::test(RequestPasswordReset::class)
            ->fillForm([
                'email' => 'guest-reset@sakina.test',
            ])
            ->call('request');

        Notification::assertNothingSent();
    }

    public function test_admin_can_reset_password_from_email_link(): void
    {
        $admin = User::factory()->create([
            'email' => 'newpass@sakina.test',
        ]);
        $admin->assignRole('super_admin');

        $token = Password::broker('users')->createToken($admin);

        Livewire::test(ResetPassword::class, [
            'email' => $admin->email,
            'token' => $token,
        ])
            ->fillForm([
                'password' => 'new-password',
                'passwordConfirmation' => 'new-password',
            ])
            ->call('resetPassword')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('new-password', $admin->fresh()->password));
    }

    public function test_login_rejects_user_without_staff_role(): void
    {
        User::factory()->create([
            'email' => 'norole@sakina.test',
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'norole@sakina.test',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest('admin');
    }

    public function test_login_rejects_inactive_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'inactive@sakina.test',
            'status' => 'suspended',
        ]);
        $admin->assignRole('super_admin');

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'inactive@sakina.test',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest('admin');
    }
}
