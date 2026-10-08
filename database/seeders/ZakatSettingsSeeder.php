<?php

namespace Database\Seeders;

use App\Settings\ZakatSettings;
use Illuminate\Database\Seeder;

class ZakatSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(ZakatSettings::class);

        foreach (ZakatSettings::defaults() as $key => $value) {
            $settings->{$key} = $value;
        }

        $settings->save();
    }
}
