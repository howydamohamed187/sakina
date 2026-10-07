<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class QuranSeeder extends Seeder
{
    public function run(): void
    {
        if (Artisan::call('quran:import') !== 0) {
            throw new RuntimeException(trim(Artisan::output()));
        }

        $this->call(QuranRecitersSeeder::class);
    }
}
