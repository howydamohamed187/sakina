<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\UserResource\Pages\CreateUser;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTrashTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin']);
    }

    public function test_admin_can_soft_delete_and_restore_a_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $user = User::factory()->create([
            'email' => 'trashed@sakina.test',
        ]);

        $this->actingAs($admin, 'admin');

        $user->delete();

        $this->assertSoftDeleted($user);

        $user->restore();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_users_table_is_visible_to_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin')
            ->get(ListUsers::getUrl())
            ->assertOk();
    }

    public function test_profile_and_settings_pages_are_visible(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee('fi-sidebar', false)
            ->assertSee('locale-switcher', false)
            ->assertSee(__('app.profile_info'));

        $this->actingAs($admin, 'admin')
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee(__('app.themes.light'));
    }

    public function test_create_admin_form_uses_combined_phone_and_section(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(CreateUser::getUrl())
            ->assertOk()
            ->assertSee('بيانات المدير')
            ->assertSee('sakina-panel.css', false)
            ->assertSee('fi-fo-phone-combined', false)
            ->assertSee('fi-fo-admin-avatar', false)
            ->assertDontSee('كود الدولة');
    }

    public function test_admin_can_be_created_without_phone_or_password(): void
    {
        $this->seed(RolesSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);
        $role = Role::findByName(Roles::ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'مدير جديد',
                'email' => 'new-manager@sakina.test',
                'roles' => [$role->id],
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(ListUsers::getUrl());

        $this->assertDatabaseHas('users', [
            'email' => 'new-manager@sakina.test',
            'phone' => null,
            'status' => 'active',
        ]);
    }
}
