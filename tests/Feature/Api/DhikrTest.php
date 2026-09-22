<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Dhikr;
use App\Support\DhikrCategories;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Tests\TestCase;

class DhikrTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Roles::ensure(Roles::CUSTOMER);
    }

    public function test_customer_can_list_and_filter_adhkar(): void
    {
        Dhikr::factory()->create([
            'title' => 'سيد الاستغفار',
            'category' => DhikrCategories::MORNING,
        ]);
        Dhikr::factory()->create([
            'title' => 'أمسينا وأمسى الملك لله',
            'category' => DhikrCategories::EVENING,
        ]);
        Dhikr::factory()->inactive()->create([
            'title' => 'ذكر مخفي',
        ]);

        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->getJson('/api/v1/adhkar')
            ->assertOk()
            ->assertJsonPath('message', 'تم جلب الأذكار.')
            ->assertJsonPath('data.adhkar.0.title', 'سيد الاستغفار')
            ->assertJsonPath('data.adhkar.0.category_label', 'أذكار الصباح')
            ->assertJsonMissing(['title' => 'ذكر مخفي']);

        $this->withToken($token)
            ->getJson('/api/v1/adhkar?category=evening')
            ->assertOk()
            ->assertJsonPath('data.adhkar.0.title', 'أمسينا وأمسى الملك لله')
            ->assertJsonCount(1, 'data.adhkar');
    }

    public function test_customer_can_favorite_and_unfavorite_dhikr(): void
    {
        $dhikr = Dhikr::factory()->create([
            'title' => 'سيد الاستغفار',
        ]);
        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/adhkar/'.$dhikr->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('message', 'تمت إضافة الذكر إلى المفضلة.')
            ->assertJsonPath('data.is_favorite', 1);

        $list = $this->withToken($token)
            ->getJson('/api/v1/adhkar')
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $list['favorites']);
        $this->assertSame($dhikr->id, $list['favorites'][0]['id']);

        $this->withToken($token)
            ->deleteJson('/api/v1/adhkar/'.$dhikr->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('data.is_favorite', 0);

        $this->assertDatabaseMissing('dhikr_favorites', [
            'dhikr_id' => $dhikr->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_guest_cannot_list_adhkar(): void
    {
        $this->getJson('/api/v1/adhkar')->assertUnauthorized();
    }
}
