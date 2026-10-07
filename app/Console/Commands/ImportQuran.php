<?php

namespace App\Console\Commands;

use App\Models\QuranSurah;
use App\Services\Quran\QuranTextImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportQuran extends Command
{
    protected $signature = 'quran:import
        {--path= : Path to the Tanzil quran_text SQL dump}
        {--verify : Only verify the stored text against the source file}';

    protected $description = 'Import the verbatim Tanzil Quran text and surah metadata';

    public function handle(QuranTextImporter $importer): int
    {
        $path = $this->option('path') ?: config('quran.source_path');

        try {
            if ($this->option('verify')) {
                $importer->assertChecksum($path);
                $importer->verify($path);
                $this->info('quran_text matches the source file exactly.');

                return self::SUCCESS;
            }

            $count = $importer->import($path);
            $this->syncSurahs();
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Imported and verified {$count} ayahs and ".QuranSurah::query()->count().' surahs.');
        $this->line('Source: Tanzil Quran Text (https://tanzil.net), CC BY 3.0.');

        return self::SUCCESS;
    }

    private function syncSurahs(): void
    {
        $counts = DB::table('quran_text')
            ->selectRaw('sura, COUNT(*) as total')
            ->groupBy('sura')
            ->pluck('total', 'sura');

        foreach (require database_path('data/quran/surahs.php') as $surah) {
            if ((int) ($counts[$surah['number']] ?? 0) !== $surah['ayahs_count']) {
                throw new RuntimeException("Ayah count mismatch for surah {$surah['number']}.");
            }

            QuranSurah::query()->updateOrCreate(['id' => $surah['number']], [
                'name_ar' => $surah['name_ar'],
                'name_en' => $surah['name_en'],
                'name_en_translation' => $surah['name_en_translation'],
                'revelation_type' => $surah['revelation_type'],
                'ayahs_count' => $surah['ayahs_count'],
            ]);
        }
    }
}
