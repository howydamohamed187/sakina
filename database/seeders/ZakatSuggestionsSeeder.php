<?php

namespace Database\Seeders;

use App\Models\ContactChannel;
use App\Models\ContactType;
use App\Support\ContactTypes;
use Illuminate\Database\Seeder;

/**
 * Zakat suggestions shown under "مقترحات للسداد" in the zakat calculator.
 * Values marked as placeholders must be replaced with verified details from the admin panel.
 */
class ZakatSuggestionsSeeder extends Seeder
{
    public function run(): void
    {
        $suggestions = [
            [
                'name' => 'جمعية رسالة',
                'kind' => ContactTypes::LINK,
                'value' => 'https://resala.org',
            ],
            [
                'name' => 'جمعية عطاء',
                'kind' => ContactTypes::LINK,
                'value' => 'https://zakat.sakina.test/ataa', // placeholder
            ],
            [
                'name' => 'حساب الزكاة الرسمي',
                'kind' => ContactTypes::ACCOUNT,
                'value' => '1234567890123', // placeholder
            ],
            [
                'name' => 'الخط الساخن للزكاة',
                'kind' => ContactTypes::HOTLINE,
                'value' => '+2025777477', // placeholder
            ],
        ];

        foreach ($suggestions as $index => $row) {
            $type = ContactType::query()->firstOrCreate(
                ['kind' => $row['kind']],
                [
                    'name' => ContactTypes::label($row['kind']),
                    'status' => 'active',
                    'sort_order' => array_search($row['kind'], ContactTypes::all(), true) + 1,
                ],
            );

            ContactChannel::query()->firstOrCreate(
                ['name' => $row['name']],
                [
                    'contact_type_id' => $type->id,
                    'type' => $row['kind'],
                    'value' => $row['value'],
                    'status' => 'active',
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
