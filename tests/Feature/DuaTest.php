<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Admin\Resources\DuaResource\Pages\CreateDua;
use App\Filament\Admin\Resources\DuaResource\Pages\ListDuas;
use App\Filament\Admin\Resources\DuaResource\Pages\ViewDua;
use App\Models\Customer;
use App\Models\Dua;
use App\Models\User;
use App\Support\DuaCategories;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class DuaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_duas_table_is_visible(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        Dua::factory()->create([
            'title' => 'دعاء الكرب',
            'body' => 'لا إله إلا الله العظيم الحليم.',
            'category' => DuaCategories::DISTRESS,
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ListDuas::getUrl())
            ->assertOk()
            ->assertSee('دعاء الكرب')
            ->assertSee('أدعية الكرب')
            ->assertSee('عدد مرات الإضافة للمفضلة');
    }

    public function test_admin_can_create_dua(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateDua::class)
            ->fillForm([
                'title' => 'دعاء عام',
                'body' => 'ربنا آتنا في الدنيا حسنة وفي الآخرة حسنة.',
                'category' => DuaCategories::GENERAL,
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('duas', [
            'title' => 'دعاء عام',
            'category' => DuaCategories::GENERAL,
            'status' => 'active',
        ]);
    }

    public function test_dua_details_show_category_and_favorites(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $dua = Dua::factory()->create([
            'title' => 'دعاء الكرب',
            'category' => DuaCategories::DISTRESS,
        ]);
        $customer = Customer::factory()->create(['name' => 'أحمد محمد علي']);
        $customer->favoriteDuas()->attach($dua->id);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ViewDua::getUrl(['record' => $dua]))
            ->assertOk()
            ->assertSee('دعاء الكرب')
            ->assertSee('أدعية الكرب')
            ->assertSee('عدد مرات الإضافة للمفضلة');

        $this->assertSame(1, $dua->favoritesCount());
    }

    public function test_customer_edit_shows_favorite_duas_tab(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);
        $customer = Customer::factory()->create(['name' => 'محمد أحمد علي']);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(EditCustomer::getUrl(['record' => $customer]))
            ->assertOk()
            ->assertSee('الأدعية المفضلة');
    }
}
