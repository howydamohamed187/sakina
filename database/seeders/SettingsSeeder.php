<?php

namespace Database\Seeders;

use App\Settings\AppearanceSettings;
use App\Settings\GeneralSettings;
use App\Support\Fonts;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $general = app(GeneralSettings::class);
        $general->app_name = [
            'ar' => 'سكينة',
            'en' => 'Sakina',
        ];
        $general->save();

        $appearance = app(AppearanceSettings::class);
        $appearance->font_family = Fonts::DEFAULT_FAMILY;
        $appearance->save();
    }
}
