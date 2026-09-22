<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Admin\Resources\DhikrResource\Pages\CreateDhikr;
use App\Filament\Admin\Resources\DhikrResource\Pages\ListDhikrs;
use App\Filament\Admin\Resources\DhikrResource\Pages\ViewDhikr;
use App\Models\Customer;
use App\Models\Dhikr;
use App\Models\User;
use App\Support\DhikrCategories;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class DhikrTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_adhkar_table_is_visible(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        Dhikr::factory()->create([
            'title' => 'سيد الاستغفار',
            'body' => 'اللهم أنت ربي لا إله إلا أنت.',
            'category' => DhikrCategories::MORNING,
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ListDhikrs::getUrl())
            ->assertOk()
            ->assertSee('سيد الاستغفار')
            ->assertSee('أذكار الصباح')
            ->assertSee('عدد مرات الإضافة للمفضلة');
    }

    public function test_admin_can_create_dhikr(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateDhikr::class)
            ->fillForm([
                'title' => 'دعاء النوم',
                'body' => 'باسمك اللهم أموت وأحيا.',
                'category' => DhikrCategories::SLEEP,
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('dhikrs', [
            'title' => 'دعاء النوم',
            'category' => DhikrCategories::SLEEP,
            'status' => 'active',
        ]);
    }

    public function test_dhikr_details_show_category_and_favorites(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $dhikr = Dhikr::factory()->create([
            'title' => 'سيد الاستغفار',
            'category' => DhikrCategories::MORNING,
        ]);
        $customer = Customer::factory()->create(['name' => 'أحمد محمد علي']);
        $customer->favoriteDhikrs()->attach($dhikr->id);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ViewDhikr::getUrl(['record' => $dhikr]))
            ->assertOk()
            ->assertSee('سيد الاستغفار')
            ->assertSee('أذكار الصباح')
            ->assertSee('عدد مرات الإضافة للمفضلة');

        $this->assertSame(1, $dhikr->favoritesCount());
    }

    public function test_customer_edit_shows_favorite_adhkar_tab(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);
        $customer = Customer::factory()->create(['name' => 'محمد أحمد علي']);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(EditCustomer::getUrl(['record' => $customer]))
            ->assertOk()
            ->assertSee('الأذكار المفضلة');
    }
}
