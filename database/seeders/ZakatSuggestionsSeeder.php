<?php

namespace Database\Seeders;

use App\Models\ContactChannel;
use App\Models\ContactType;
use App\Support\ContactTypes;
use Illuminate\Database\Seeder;

class ZakatSuggestionsSeeder extends Seeder
{
    public function run(): void
    {
        $suggestions = [
            ContactTypes::HOTLINE => [
                'name' => 'الخط الساخن للزكاة',
                'value' => '+2025777477',
            ],
            ContactTypes::PHONE => [
                'name' => 'هاتف صندوق الزكاة',
                'value' => '+201012345678',
            ],
            ContactTypes::ACCOUNT => [
                'name' => 'حساب الزكاة الرسمي',
                'value' => '1234567890123',
            ],
            ContactTypes::LINK => [
                'name' => 'بوابة دفع الزكاة',
                'value' => 'https://zakat.sakina.test',
            ],
        ];

        foreach ($suggestions as $kind => $row) {
            $type = ContactType::query()->firstOrCreate(
                ['kind' => $kind],
                [
                    'name' => ContactTypes::label($kind),
                    'status' => 'active',
                    'sort_order' => array_search($kind, ContactTypes::all(), true) + 1,
                ],
            );

            ContactChannel::query()->firstOrCreate(
                ['name' => $row['name']],
                [
                    'contact_type_id' => $type->id,
                    'type' => $kind,
                    'value' => $row['value'],
                    'status' => 'active',
                    'sort_order' => $type->sort_order,
                ],
            );
        }
    }
}
