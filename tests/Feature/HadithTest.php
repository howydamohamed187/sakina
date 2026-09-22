<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Admin\Resources\HadithResource\Pages\CreateHadith;
use App\Filament\Admin\Resources\HadithResource\Pages\ListHadiths;
use App\Filament\Admin\Resources\HadithResource\Pages\ViewHadith;
use App\Models\Customer;
use App\Models\Hadith;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class HadithTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_hadiths_table_is_visible(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        Hadith::factory()->create([
            'title' => 'حديث عن الأعمال',
            'body' => 'إنما الأعمال بالنيات.',
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ListHadiths::getUrl())
            ->assertOk()
            ->assertSee('حديث عن الأعمال')
            ->assertSee('إنما الأعمال بالنيات.')
            ->assertSee('عدد مرات الإضافة للمفضلة');
    }

    public function test_admin_can_create_hadith(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateHadith::class)
            ->fillForm([
                'title' => 'حديث عن الصوم',
                'body' => 'من صام رمضان إيمانًا واحتسابًا غفر له ما تقدم من ذنبه.',
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('hadiths', [
            'title' => 'حديث عن الصوم',
            'status' => 'active',
        ]);
    }

    public function test_hadith_details_show_favorite_count(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $hadith = Hadith::factory()->create([
            'title' => 'حديث عن الأعمال',
            'body' => 'إنما الأعمال بالنيات، وإنما لكل امرئ ما نوى.',
        ]);
        $customer = Customer::factory()->create(['name' => 'أحمد محمد علي']);
        $customer->favoriteHadiths()->attach($hadith->id);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ViewHadith::getUrl(['record' => $hadith]))
            ->assertOk()
            ->assertSee('حديث عن الأعمال')
            ->assertSee('إنما الأعمال بالنيات')
            ->assertSee('عدد مرات الإضافة للمفضلة');

        $this->assertSame(1, $hadith->favoritesCount());
    }

    public function test_customer_edit_shows_favorite_hadiths_tab(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);
        $customer = Customer::factory()->create(['name' => 'محمد أحمد علي']);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(EditCustomer::getUrl(['record' => $customer]))
            ->assertOk()
            ->assertSee('الأحاديث المفضلة');
    }
}
