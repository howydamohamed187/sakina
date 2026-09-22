<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminSeeder::class,
            SettingsSeeder::class,
            ZakatSuggestionsSeeder::class,
            DailyQuestionsSeeder::class,
            HadithsSeeder::class,
            AdhkarSeeder::class,
            DuasSeeder::class,
        ]);
    }
}
