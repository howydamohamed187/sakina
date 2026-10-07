<?php

namespace App\Services\Quran;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Loads the Tanzil `quran_text` dump into the `quran_text` table without altering a single character.
 */
class QuranTextImporter
{
    public const TOTAL_AYAHS = 6236;

    public const TOTAL_SURAHS = 114;

    private const ROW_PATTERN = "/^\((\d+), (\d+), (\d+), '((?:[^'\\\\]|\\\\.|'')*)'\)[,;]\s*$/u";

    /**
     * @return list<array{index: int, sura: int, aya: int, text: string}>
     */
    public function parse(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Quran source file not found: {$path}");
        }

        $rows = [];
        $handle = fopen($path, 'rb');

        try {
            while (($line = fgets($handle)) !== false) {
                if (! str_starts_with($line, '(')) {
                    continue;
                }

                if (! preg_match(self::ROW_PATTERN, rtrim($line, "\r\n"), $match)) {
                    throw new RuntimeException('Unrecognised quran_text row: '.mb_substr($line, 0, 80));
                }

                $rows[] = [
                    'index' => (int) $match[1],
                    'sura' => (int) $match[2],
                    'aya' => (int) $match[3],
                    'text' => $this->unescape($match[4]),
                ];
            }
        } finally {
            fclose($handle);
        }

        $this->assertComplete($rows);

        return $rows;
    }

    public function import(string $path): int
    {
        $this->assertChecksum($path);
        $rows = $this->parse($path);

        DB::transaction(function () use ($rows): void {
            DB::table('quran_text')->delete();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('quran_text')->insert($chunk);
            }
        });

        $this->verify($path, $rows);

        return count($rows);
    }

    /**
     * Compares every stored ayah byte-for-byte against the source file.
     *
     * @param  list<array{index: int, sura: int, aya: int, text: string}>|null  $rows
     */
    public function verify(string $path, ?array $rows = null): void
    {
        $rows ??= $this->parse($path);
        $stored = DB::table('quran_text')->orderBy('index')->get(['index', 'sura', 'aya', 'text'])->keyBy('index');

        if ($stored->count() !== count($rows)) {
            throw new RuntimeException('Expected '.count($rows)." ayahs in quran_text, found {$stored->count()}.");
        }

        foreach ($rows as $row) {
            $db = $stored->get($row['index']);

            if (! $db || (int) $db->sura !== $row['sura'] || (int) $db->aya !== $row['aya'] || $db->text !== $row['text']) {
                throw new RuntimeException("quran_text row {$row['index']} ({$row['sura']}:{$row['aya']}) does not match the source file.");
            }
        }
    }

    public function assertChecksum(string $path): void
    {
        $expected = config('quran.source_sha256');

        if ($expected && hash_file('sha256', $path) !== strtolower($expected)) {
            throw new RuntimeException('Quran source file checksum mismatch; the file must not be modified.');
        }
    }

    /**
     * @param  list<array{index: int, sura: int, aya: int, text: string}>  $rows
     */
    private function assertComplete(array $rows): void
    {
        $surahs = count(array_unique(array_column($rows, 'sura')));

        if (count($rows) !== self::TOTAL_AYAHS || $surahs !== self::TOTAL_SURAHS) {
            throw new RuntimeException('Quran source is incomplete: '.count($rows)." ayahs across {$surahs} surahs.");
        }
    }

    private function unescape(string $value): string
    {
        if (! str_contains($value, '\\') && ! str_contains($value, "''")) {
            return $value;
        }

        return preg_replace_callback("/\\\\(.)|''/su", function (array $m): string {
            if ($m[0] === "''") {
                return "'";
            }

            return match ($m[1]) {
                'n' => "\n",
                'r' => "\r",
                't' => "\t",
                '0' => "\0",
                default => $m[1],
            };
        }, $value);
    }
}
