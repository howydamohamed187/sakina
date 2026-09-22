<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Hadith;
use App\Support\Roles;
use Database\Seeders\RolesSeeder;
use Tests\TestCase;

class HadithTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        Roles::ensure(Roles::CUSTOMER);
    }

    public function test_customer_can_list_hadiths_and_search(): void
    {
        Hadith::factory()->create([
            'title' => 'حديث عن الأعمال',
            'body' => 'إنما الأعمال بالنيات.',
        ]);
        Hadith::factory()->create([
            'title' => 'حديث عن الصوم',
            'body' => 'من صام رمضان إيمانًا واحتسابًا.',
        ]);
        Hadith::factory()->inactive()->create([
            'title' => 'حديث مخفي',
        ]);

        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->getJson('/api/v1/hadiths')
            ->assertOk()
            ->assertJsonPath('message', 'تم جلب الأحاديث.')
            ->assertJsonPath('data.hadiths.0.title', 'حديث عن الأعمال')
            ->assertJsonMissing(['title' => 'حديث مخفي']);

        $this->withToken($token)
            ->getJson('/api/v1/hadiths?q=الصوم')
            ->assertOk()
            ->assertJsonPath('data.hadiths.0.title', 'حديث عن الصوم')
            ->assertJsonCount(1, 'data.hadiths');
    }

    public function test_customer_can_favorite_and_unfavorite_hadith(): void
    {
        $hadith = Hadith::factory()->create([
            'title' => 'حديث عن الأعمال',
        ]);
        $customer = Customer::factory()->create();
        $token = $customer->createToken('customer')->plainTextToken;

        $this->withToken($token)
            ->withHeader('X-Locale', 'ar')
            ->postJson('/api/v1/hadiths/'.$hadith->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('message', 'تمت إضافة الحديث إلى المفضلة.')
            ->assertJsonPath('data.is_favorite', 1)
            ->assertJsonPath('data.favorites_count', 1);

        $list = $this->withToken($token)
            ->getJson('/api/v1/hadiths')
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $list['favorites']);
        $this->assertSame($hadith->id, $list['favorites'][0]['id']);
        $this->assertSame(1, $list['hadiths'][0]['is_favorite']);

        $this->withToken($token)
            ->deleteJson('/api/v1/hadiths/'.$hadith->id.'/favorite')
            ->assertOk()
            ->assertJsonPath('data.is_favorite', 0);

        $this->assertDatabaseMissing('hadith_favorites', [
            'hadith_id' => $hadith->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_guest_cannot_list_hadiths(): void
    {
        $this->getJson('/api/v1/hadiths')->assertUnauthorized();
    }
}
