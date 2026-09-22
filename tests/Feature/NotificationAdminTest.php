<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SendNotifications;
use App\Filament\Admin\Resources\NotificationResource\Pages\ListNotifications;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\AdminMessageNotification;
use App\Support\Permissions;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_admin_can_send_notification_to_all_customers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $first = Customer::factory()->create(['name' => 'محمد أحمد علي']);
        $second = Customer::factory()->create(['name' => 'خالد حسن محمود']);

        $this->actingAs($admin, 'admin');

        Livewire::test(SendNotifications::class)
            ->fillForm([
                'title' => [
                    'ar' => 'تنبيه هام',
                    'en' => 'Important notice',
                    'ckb' => 'ئاگاداری گرنگ',
                ],
                'body' => [
                    'ar' => 'يرجى مراجعة التطبيق.',
                    'en' => 'Please check the app.',
                    'ckb' => 'تکایە ئەپەکە بپشکنە.',
                ],
                'recipient_type' => 'customers',
                'notification_type' => 'all',
            ])
            ->call('submit')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame(1, $first->notifications()->count());
        $this->assertSame(1, $second->notifications()->count());
        $this->assertSame('تنبيه هام', $first->fresh()->notifications()->first()->data['title']);
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_admin_can_send_notification_to_specific_admins(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $target = User::factory()->create(['name' => 'مدير المحتوى']);
        $target->assignRole(Roles::ADMIN);

        $other = User::factory()->create();
        $other->assignRole(Roles::ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(SendNotifications::class)
            ->fillForm([
                'title' => [
                    'ar' => 'رسالة للمديرين',
                    'en' => 'Admin message',
                ],
                'body' => [
                    'ar' => 'تحديث جديد في اللوحة.',
                    'en' => 'A panel update.',
                ],
                'recipient_type' => 'admins',
                'notification_type' => 'specific',
                'notifiable' => [$target->id],
            ])
            ->call('submit')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame(1, $target->notifications()->count());
        $this->assertSame(0, $other->notifications()->count());
        $this->assertSame('رسالة للمديرين', $target->fresh()->notifications()->first()->data['title']);
    }

    public function test_specific_recipient_select_lists_customers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        Customer::factory()->create([
            'name' => 'محمود علي حسن',
            'email' => 'mahmoud@sakina.test',
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(SendNotifications::class)
            ->fillForm([
                'recipient_type' => 'customers',
                'notification_type' => 'specific',
            ])
            ->assertSee('mahmoud@sakina.test');
    }

    public function test_notification_inbox_shows_only_current_admin_records(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $other = User::factory()->create();
        $other->assignRole(Roles::ADMIN);

        $admin->notify(new AdminMessageNotification(
            ['ar' => 'إشعاري الخاص'],
            ['ar' => 'هذا الإشعار لي فقط.'],
        ));

        $other->notify(new AdminMessageNotification(
            ['ar' => 'إشعار مدير آخر'],
            ['ar' => 'لا يجب أن يظهر.'],
        ));

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ListNotifications::getUrl())
            ->assertOk()
            ->assertSee('إشعاري الخاص')
            ->assertDontSee('إشعار مدير آخر');
    }

    public function test_admin_can_delete_own_notification(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $admin->notify(new AdminMessageNotification(
            ['ar' => 'إشعار للحذف'],
            ['ar' => 'احذف هذا.'],
        ));

        $notification = $admin->notifications()->first();

        $this->actingAs($admin, 'admin');

        Livewire::test(ListNotifications::class)
            ->callTableAction('delete', $notification)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_admin_without_permission_cannot_open_send_page(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::ACCESS_ADMIN);

        $this->actingAs($user, 'admin')
            ->get(SendNotifications::getUrl())
            ->assertForbidden();
    }
}
