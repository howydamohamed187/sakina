<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\Admin\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Admin\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Models\Customer;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_registered_customer_receives_customer_role(): void
    {
        $this->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/auth/register', [
                'name' => 'محمد أحمد علي',
                'email' => 'role-customer@sakina.test',
                'password' => 'secret123',
                'location' => 'القاهرة',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ])
            ->assertCreated();

        $customer = Customer::query()->where('email', 'role-customer@sakina.test')->first();

        $this->assertNotNull($customer);
        $this->assertTrue($customer->hasRole(Roles::CUSTOMER));
    }

    public function test_admin_sidebar_shows_user_and_permission_groups(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get('/admin')
            ->assertOk()
            ->assertSee('إدارة المستخدمين')
            ->assertSee('المدراء')
            ->assertSee('العملاء')
            ->assertSee('الزكاة')
            ->assertSee('نوع التواصل')
            ->assertSee('مقترحات الزكاة')
            ->assertSee('سؤال اليوم')
            ->assertSee('الأحاديث النبوية')
            ->assertSee('الأذكار')
            ->assertSee('الأدعية')
            ->assertSee('الإشعارات')
            ->assertSee('إرسال إشعار')
            ->assertSee('إشعاراتي')
            ->assertSee('إدارة الصلاحيات')
            ->assertSee('الأدوار')
            ->assertSee('الإعدادات');
    }

    public function test_role_page_shows_all_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(CreateRole::getUrl())
            ->assertOk()
            ->assertSee('الإضافة')
            ->assertSee('التعديل')
            ->assertSee('الحذف')
            ->assertSee('الاستعادة')
            ->assertSee('الأخرى')
            ->assertSee('المدراء')
            ->assertSee('عرض مقترحات الزكاة')
            ->assertSee('عرض نوع التواصل')
            ->assertSee('الدخول للوحة التحكم')
            ->assertSee('الإعدادات');
    }

    public function test_role_permissions_can_be_saved_by_group(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $createPermission = Permission::query()
            ->where('name', Permissions::name(Permissions::RESOURCE_CUSTOMERS, Permissions::ACTION_CREATE))
            ->first();
        $restorePermission = Permission::query()
            ->where('name', Permissions::name(Permissions::RESOURCE_CUSTOMERS, Permissions::ACTION_RESTORE))
            ->first();

        $this->assertNotNull($createPermission);
        $this->assertNotNull($restorePermission);

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'manager',
                'permission_groups' => [
                    'create' => [(string) $createPermission->id],
                    'restore' => [(string) $restorePermission->id],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::query()->where('name', 'manager')->first();

        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo($createPermission->name));
        $this->assertTrue($role->hasPermissionTo($restorePermission->name));
        $this->assertFalse($role->hasPermissionTo(Permissions::ACCESS_ADMIN));
    }

    public function test_admin_role_only_sees_assigned_resources(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get('/admin')
            ->assertOk()
            ->assertSee('العملاء')
            ->assertSee('مقترحات الزكاة')
            ->assertSee('نوع التواصل')
            ->assertDontSee('المدراء')
            ->assertDontSee('الأدوار');

        $this->actingAs($admin, 'admin')
            ->get(ListCustomers::getUrl())
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(ListUsers::getUrl())
            ->assertForbidden();

        $this->actingAs($admin, 'admin')
            ->get(ListRoles::getUrl())
            ->assertForbidden();
    }

    public function test_roles_table_shows_types_and_system_roles(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ListRoles::getUrl())
            ->assertOk()
            ->assertSee('super_admin')
            ->assertSee('admin')
            ->assertSee('customer')
            ->assertSee('لوحة التحكم')
            ->assertSee('التطبيق')
            ->assertSee('الأذونات')
            ->assertSee('المستخدمون');
    }

    public function test_user_with_admin_role_can_access_panel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertOk();
    }

    public function test_user_with_only_a_permission_can_access_panel(): void
    {
        $permission = Permission::findOrCreate('access_admin', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        $this->actingAs($user, 'admin')
            ->get('/admin')
            ->assertOk();
    }

    public function test_user_without_staff_role_or_permission_cannot_access_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'admin')
            ->get('/admin')
            ->assertForbidden();
    }
}
