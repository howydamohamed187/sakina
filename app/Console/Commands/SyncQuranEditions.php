<?php

namespace App\Console\Commands;

use App\Models\QuranSurah;
use App\Services\Providers\ProviderException;
use App\Services\Quran\QuranEditionSync;
use Illuminate\Console\Command;

class SyncQuranEditions extends Command
{
    protected $signature = 'quran:sync-editions
        {--surah=* : Only these surah numbers}
        {--force : Re-download even if already stored}';

    protected $description = 'Download Quran translations and tafsir from the configured provider into the local tables';

    public function handle(QuranEditionSync $sync): int
    {
        $surahs = $this->option('surah') ?: QuranSurah::query()->orderBy('id')->pluck('id')->all();
        $failures = 0;

        $this->withProgressBar($surahs, function ($surah) use ($sync, &$failures): void {
            foreach (QuranEditionSync::types() as $type => $model) {
                foreach ((array) config("quran.editions.{$type}") as $locale => $edition) {
                    try {
                        $sync->sync((int) $surah, $model, (string) $locale, (string) $edition, (bool) $this->option('force'), respectBackoff: false);
                    } catch (ProviderException $e) {
                        $failures++;
                        $this->newLine();
                        $this->warn("Surah {$surah} / {$edition}: {$e->getMessage()}");
                    }
                }
            }
        });

        $this->newLine(2);

        if ($failures > 0) {
            $this->error("{$failures} edition download(s) failed. Run the command again to retry.");

            return self::FAILURE;
        }

        $this->info('Quran translations and tafsir are up to date.');

        return self::SUCCESS;
    }
}
