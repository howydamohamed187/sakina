<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Dua;
use App\Support\DuaCategories;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Tests\TestCase;

class DuaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Roles::ensure(Roles::CUSTOMER);
    }

    public function test_customer_can_list_and_filter_duas(): void
    {
        Dua::factory()->create([
            'title' => 'دعاء الكرب',
            'category' => DuaCategories::DISTRESS,
        ]);
        Dua::factory()->create([
            'title' => 'دعاء عام',
            'category' => DuaCategories::GENERAL,
        ]);
        Dua::factory()->inactive()->create([
            'title' => 'دعاء مخفي',
        ]);

        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->getJson('/api/v1/duas')
            ->assertOk()
            ->assertJsonPath('message', 'تم جلب الأدعية.')
            ->assertJsonPath('data.duas.0.title', 'دعاء الكرب')
            ->assertJsonPath('data.duas.0.category_label', 'أدعية الكرب')
            ->assertJsonMissing(['title' => 'دعاء مخفي']);

        $this->withToken($token)
            ->getJson('/api/v1/duas?category=general')
            ->assertOk()
            ->assertJsonPath('data.duas.0.title', 'دعاء عام')
            ->assertJsonCount(1, 'data.duas');
    }

    public function test_customer_can_favorite_and_unfavorite_dua(): void
    {
        $dua = Dua::factory()->create([
            'title' => 'دعاء الكرب',
        ]);
        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/duas/'.$dua->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('message', 'تمت إضافة الدعاء إلى المفضلة.')
            ->assertJsonPath('data.is_favorite', 1);

        $list = $this->withToken($token)
            ->getJson('/api/v1/duas')
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $list['favorites']);
        $this->assertSame($dua->id, $list['favorites'][0]['id']);

        $this->withToken($token)
            ->deleteJson('/api/v1/duas/'.$dua->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('data.is_favorite', 0);

        $this->assertDatabaseMissing('dua_favorites', [
            'dua_id' => $dua->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_guest_cannot_list_duas(): void
    {
        $this->getJson('/api/v1/duas')->assertUnauthorized();
    }
}
