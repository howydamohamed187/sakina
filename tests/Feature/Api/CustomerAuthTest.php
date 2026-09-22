<?php

namespace Tests\Feature\Api;

use App\Filament\Admin\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Admin\Resources\CustomerResource\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\OtpService;
use App\Settings\DeveloperSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_with_password_and_receive_token(): void
    {
        Notification::fake();

        $response = $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/register', [
                'name' => 'محمد أحمد علي',
                'email' => 'customer@sakina.test',
                'password' => 'secret123',
                'location' => 'القاهرة',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ])
            ->assertCreated()
            ->assertJsonPath('status', 201)
            ->assertJsonPath('message', 'تم إنشاء الحساب. تم إرسال رمز التحقق إلى بريدك الإلكتروني. أدخل الرمز في خطوة التفعيل لإكمال التسجيل.')
            ->assertJsonPath('data.email', 'customer@sakina.test')
            ->assertJsonPath('data.name', 'محمد أحمد علي')
            ->assertJsonPath('data.location', 'القاهرة')
            ->assertJsonPath('data.latitude', 30.0444)
            ->assertJsonPath('data.longitude', 31.2357)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.email_verified', 0)
            ->assertJsonPath('data.need_activation', 1)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.customer');

        $this->assertNotEmpty($response->json('data.token'));

        $this->assertDatabaseHas('customers', [
            'email' => 'customer@sakina.test',
            'location' => 'القاهرة',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $this->assertNotNull(
            app(OtpService::class)->peek('customer@sakina.test')
        );

        $this->assertDatabaseHas('verification_codes', [
            'customer_id' => Customer::query()->where('email', 'customer@sakina.test')->value('id'),
        ]);
    }

    public function test_register_requires_password(): void
    {
        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/register', [
                'name' => 'محمد أحمد علي',
                'email' => 'nopass@sakina.test',
                'location' => 'القاهرة',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 422)
            ->assertJsonPath('errors.password', 'حقل كلمة المرور مطلوب.');
    }

    public function test_register_stores_static_code_when_random_otp_is_disabled(): void
    {
        Notification::fake();

        $developer = app(DeveloperSettings::class);
        $developer->otp_code_is_random = false;
        $developer->save();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'احمد جمال بدير',
            'email' => 'Lucius_Yost39@yahoo.com',
            'password' => 'secret123',
            'location' => 'الغربيه المحله الكبري',
            'latitude' => 30.9696,
            'longitude' => 31.1669,
        ])
            ->assertCreated()
            ->assertJsonPath('data.need_activation', 1);

        $customer = Customer::query()->where('email', 'Lucius_Yost39@yahoo.com')->first();

        $this->assertDatabaseHas('verification_codes', [
            'customer_id' => $customer->id,
            'code' => '1234',
        ]);

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => $customer->email,
            'code' => 1234,
        ])
            ->assertOk()
            ->assertJsonPath('data.valid', 1)
            ->assertJsonPath('data.need_activation', 1);

        $this->assertSame('pending', $customer->fresh()->status);

        $this->postJson('/api/v1/auth/verify-code', [
            'email' => $customer->email,
            'code' => 1234,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.email_verified', 1)
            ->assertJsonPath('data.need_activation', 0);

        $this->assertNotNull(
            VerificationCode::query()->where('customer_id', $customer->id)->whereNotNull('used_at')->first()
        );
    }

    public function test_register_stores_random_four_digit_code_when_enabled(): void
    {
        Notification::fake();

        $developer = app(DeveloperSettings::class);
        $developer->otp_code_is_random = true;
        $developer->save();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'محمد أحمد علي',
            'email' => 'random-otp@sakina.test',
            'password' => 'secret123',
            'location' => 'القاهرة',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ])->assertCreated();

        $code = app(OtpService::class)->peek('random-otp@sakina.test');

        $this->assertMatchesRegularExpression('/^\d{4}$/', (string) $code);
        $this->assertDatabaseHas('verification_codes', [
            'code' => $code,
        ]);
    }

    public function test_suspended_customer_cannot_login(): void
    {
        $customer = Customer::factory()->suspended()->create([
            'email' => 'blocked@sakina.test',
            'password' => 'secret123',
        ]);

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/login', [
                'email' => $customer->email,
                'password' => 'secret123',
            ])
            ->assertStatus(403)
            ->assertJsonPath('status', 403)
            ->assertJsonPath('message', 'الحساب غير نشط.');
    }

    public function test_register_rejects_duplicate_email_with_translated_message(): void
    {
        Customer::factory()->create([
            'email' => 'customer@sakina.test',
        ]);

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/register', [
                'name' => 'محمد أحمد علي',
                'email' => 'customer@sakina.test',
                'password' => 'secret123',
                'location' => 'القاهرة',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 422)
            ->assertJsonPath('message', 'قيمة حقل البريد الإلكتروني مُستخدمة من قبل')
            ->assertJsonPath('errors.email', 'قيمة حقل البريد الإلكتروني مُستخدمة من قبل')
            ->assertJsonPath('data', []);
    }

    public function test_register_validation_uses_flat_error_envelope(): void
    {
        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/register', [])
            ->assertStatus(422)
            ->assertJsonPath('status', 422)
            ->assertJsonPath('message', 'حقل الاسم مطلوب.')
            ->assertJsonPath('errors.name', 'حقل الاسم مطلوب.')
            ->assertJsonPath('errors.email', 'حقل البريد الإلكتروني مطلوب.')
            ->assertJsonPath('errors.password', 'حقل كلمة المرور مطلوب.')
            ->assertJsonPath('errors.location', 'حقل الموقع الجغرافي مطلوب.')
            ->assertJsonPath('errors.latitude', 'حقل خط العرض مطلوب.')
            ->assertJsonPath('errors.longitude', 'حقل خط الطول مطلوب.')
            ->assertJsonPath('data', []);
    }

    public function test_register_requires_a_three_part_name(): void
    {
        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/register', [
                'name' => 'محمد أحمد',
                'email' => 'triple@sakina.test',
                'password' => 'secret123',
                'location' => 'القاهرة',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 422)
            ->assertJsonPath('message', 'حقل الاسم يجب أن يكون ثلاثياً.')
            ->assertJsonPath('errors.name', 'حقل الاسم يجب أن يكون ثلاثياً.');
    }

    public function test_active_customer_login_returns_token(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'login@sakina.test',
            'password' => 'secret123',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $customer->email,
            'password' => 'secret123',
        ])
            ->assertOk()
            ->assertJsonPath('data.email', $customer->email)
            ->assertJsonPath('data.need_activation', 0)
            ->assertJsonPath('data.email_verified', 1)
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertNotEmpty($login->json('data.token'));
    }

    public function test_unverified_customer_cannot_login_until_activated(): void
    {
        Notification::fake();

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/register', [
                'name' => 'محمد أحمد علي',
                'email' => 'activate@sakina.test',
                'password' => 'secret123',
                'location' => 'القاهرة',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ])
            ->assertCreated()
            ->assertJsonPath('data.need_activation', 1);

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/login', [
                'email' => 'activate@sakina.test',
                'password' => 'secret123',
            ])
            ->assertStatus(400)
            ->assertJsonPath('status', 400)
            ->assertJsonPath('message', 'حسابك يحتاج إلى التفعيل. تم إرسال رمز التحقق إلى بريدك الإلكتروني.')
            ->assertJsonPath('data.need_activation', 1);

        $code = app(OtpService::class)->peek('activate@sakina.test');

        $this->postJson('/api/v1/auth/verify-code', [
            'email' => 'activate@sakina.test',
            'code' => $code,
        ])
            ->assertOk()
            ->assertJsonPath('data.email_verified', 1)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.need_activation', 0);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'activate@sakina.test',
            'password' => 'secret123',
        ])
            ->assertOk()
            ->assertJsonPath('data.need_activation', 0);
    }

    public function test_customer_can_resend_code(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create([
            'email' => 'resend@sakina.test',
        ]);

        $this->postJson('/api/v1/auth/resend-code', [
            'email' => $customer->email,
        ])
            ->assertOk()
            ->assertJsonPath('data.email', $customer->email);
    }

    public function test_verification_code_must_be_four_digits(): void
    {
        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/verify-code', [
                'email' => 'badotp@sakina.test',
                'code' => 'abcd',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.code', 'حقل رمز التحقق يجب أن يكون 4 أرقام.');

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/verify-otp', [
                'email' => 'badotp@sakina.test',
                'code' => '12345',
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.code', 'حقل رمز التحقق يجب أن يكون 4 أرقام.');
    }

    public function test_invalid_otp_is_rejected(): void
    {
        Customer::factory()->create([
            'email' => 'badotp@sakina.test',
        ]);

        $this->withHeader('X-Locale', 'en')
            ->postJson('/api/v1/auth/verify-code', [
                'email' => 'badotp@sakina.test',
                'code' => 1111,
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', 422)
            ->assertJsonPath('message', 'The verification code is invalid or expired.')
            ->assertJsonPath('data', []);
    }

    public function test_customer_can_logout(): void
    {
        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'تم تسجيل الخروج بنجاح.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_customer_account_is_force_deleted(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'remove@sakina.test',
        ]);
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->deleteJson('/api/v1/auth/account')
            ->assertOk()
            ->assertJsonPath('message', 'تم حذف الحساب نهائياً.');

        $this->assertDatabaseMissing('customers', [
            'email' => 'remove@sakina.test',
        ]);
        $this->assertSame(0, Customer::withTrashed()->where('email', 'remove@sakina.test')->count());
    }

    public function test_customer_can_update_profile_and_location(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'profile-customer@sakina.test',
            'location' => 'القاهرة',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->putJson('/api/v1/profile', [
                'name' => 'محمد أحمد علي',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'محمد أحمد علي')
            ->assertJsonPath('message', 'تم تحديث الملف الشخصي بنجاح.');

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->putJson('/api/v1/location', [
                'latitude' => 30.9696,
                'longitude' => 31.1669,
            ])
            ->assertOk()
            ->assertJsonPath('data.location', 'القاهرة')
            ->assertJsonPath('data.latitude', 30.9696)
            ->assertJsonPath('data.longitude', 31.1669)
            ->assertJsonPath('message', 'تم تحديث الموقع بنجاح.');
    }

    public function test_customer_can_reset_password_with_code(): void
    {
        Notification::fake();

        $developer = app(DeveloperSettings::class);
        $developer->otp_code_is_random = false;
        $developer->save();

        $customer = Customer::factory()->create([
            'email' => 'Some_Rutherford@yahoo.com',
            'password' => 'old-secret',
        ]);

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/forgot-password', [
                'email' => $customer->email,
            ])
            ->assertOk()
            ->assertJsonPath('message', 'تم إرسال رمز استعادة كلمة المرور إلى بريدك الإلكتروني.')
            ->assertJsonPath('data.code', '1234');

        $this->assertDatabaseHas('verification_codes', [
            'customer_id' => $customer->id,
            'code' => '1234',
            'type' => 'password_reset',
        ]);

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $customer->email,
            'code' => 1234,
            'password' => 'newSecret',
            'password_confirmation' => 'newSecret',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'تم تغيير كلمة المرور بنجاح.');

        $this->postJson('/api/v1/auth/login', [
            'email' => $customer->email,
            'password' => 'old-secret',
        ])->assertUnauthorized();

        $this->postJson('/api/v1/auth/login', [
            'email' => $customer->email,
            'password' => 'newSecret',
        ])->assertOk();
    }

    public function test_login_and_password_reset_require_customer_role(): void
    {
        Notification::fake();

        $customer = Customer::factory()->create([
            'email' => 'norole@sakina.test',
            'password' => 'secret123',
        ]);
        $customer->roles()->detach();

        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/login', [
                'email' => $customer->email,
                'password' => 'secret123',
            ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'بيانات الدخول غير صحيحة.');

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $customer->email,
        ])
            ->assertUnauthorized();

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $customer->email,
            'code' => 1234,
            'password' => 'newSecret',
            'password_confirmation' => 'newSecret',
        ])
            ->assertUnauthorized();
    }

    public function test_admin_customer_form_uses_map_without_role_or_coordinates(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(CreateCustomer::getUrl())
            ->assertOk()
            ->assertSee('العنوان')
            ->assertSee('الموقع الجغرافي')
            ->assertSee('كلمة المرور')
            ->assertDontSee('خط العرض')
            ->assertDontSee('خط الطول');

        Livewire::test(CreateCustomer::class)
            ->assertFormFieldIsHidden('password_confirmation')
            ->fillForm(['password' => 'secret123'])
            ->assertFormFieldIsVisible('password_confirmation')
            ->assertSee('تأكيد كلمة المرور');
    }

    public function test_customers_table_is_visible_and_supports_soft_delete(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $customer = Customer::factory()->create([
            'name' => 'عميل للوحة',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(ListCustomers::getUrl())
            ->assertOk()
            ->assertSee('عميل للوحة');

        $customer->delete();

        $this->assertSoftDeleted($customer);
    }
}
