<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\ContactChannelResource\Pages\CreateContactChannel;
use App\Filament\Admin\Resources\ContactChannelResource\Pages\ListContactChannels;
use App\Filament\Admin\Resources\ContactTypeResource\Pages\CreateContactType;
use App\Models\ContactChannel;
use App\Models\ContactType;
use App\Models\User;
use App\Support\ContactTypes;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Database\Seeders\ZakatSuggestionsSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class ContactChannelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_contact_channels_table_is_visible(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        ContactChannel::factory()->create([
            'name' => 'صندوق الزكاة',
        ]);

        $this->actingAs($admin, 'admin')
            ->withHeader('X-Locale', 'ar')
            ->get(ListContactChannels::getUrl())
            ->assertOk()
            ->assertSee('صندوق الزكاة')
            ->assertSee('مقترحات الزكاة');
    }

    public function test_admin_can_create_contact_type(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateContactType::class)
            ->fillForm([
                'name' => 'خط ساخن الزكاة',
                'kind' => ContactTypes::HOTLINE,
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contact_types', [
            'name' => 'خط ساخن الزكاة',
            'kind' => ContactTypes::HOTLINE,
        ]);
    }

    public function test_admin_can_create_link_contact_channel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);
        $type = ContactType::query()->where('kind', ContactTypes::LINK)->firstOrFail();

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateContactChannel::class)
            ->fillForm([
                'contact_type_id' => $type->id,
                'name' => 'بوابة الزكاة',
                'link_value' => 'https://zakat.example.com',
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contact_channels', [
            'name' => 'بوابة الزكاة',
            'contact_type_id' => $type->id,
            'type' => ContactTypes::LINK,
            'value' => 'https://zakat.example.com',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_create_phone_contact_channel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::SUPER_ADMIN);
        $type = ContactType::query()->where('kind', ContactTypes::PHONE)->firstOrFail();

        $this->actingAs($admin, 'admin');

        Livewire::test(CreateContactChannel::class)
            ->fillForm([
                'contact_type_id' => $type->id,
                'name' => 'دعم الزكاة',
                'phone_value' => [
                    'country' => 'EG',
                    'national' => '1012345678',
                ],
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contact_channels', [
            'name' => 'دعم الزكاة',
            'contact_type_id' => $type->id,
            'type' => ContactTypes::PHONE,
            'value' => '+201012345678',
        ]);
    }

    public function test_customer_api_lists_active_contact_channels_only(): void
    {
        ContactChannel::factory()->create([
            'name' => 'رابط ظاهر',
            'status' => 'active',
        ]);
        ContactChannel::factory()->create([
            'name' => 'رابط مخفي',
            'status' => 'suspended',
        ]);

        $this->getJson('/api/v1/contact-channels')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'رابط ظاهر')
            ->assertJsonMissing(['name' => 'رابط مخفي']);
    }

    public function test_zakat_suggestions_seeder_creates_real_channels(): void
    {
        $this->seed(ZakatSuggestionsSeeder::class);

        $this->assertDatabaseHas('contact_channels', [
            'name' => 'الخط الساخن للزكاة',
            'type' => ContactTypes::HOTLINE,
            'value' => '+2025777477',
        ]);
        $this->assertDatabaseHas('contact_channels', [
            'name' => 'هاتف صندوق الزكاة',
            'type' => ContactTypes::PHONE,
        ]);
        $this->assertDatabaseHas('contact_channels', [
            'name' => 'حساب الزكاة الرسمي',
            'type' => ContactTypes::ACCOUNT,
        ]);
        $this->assertDatabaseHas('contact_channels', [
            'name' => 'بوابة دفع الزكاة',
            'type' => ContactTypes::LINK,
            'value' => 'https://zakat.sakina.test',
        ]);
    }
}
