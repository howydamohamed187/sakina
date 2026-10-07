<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Dhikr;
use App\Models\DhikrProgress;
use Illuminate\Support\Facades\DB;

class TasbeehProgressService
{
    /**
     * Flutter counts locally and syncs the latest snapshot. total_count only grows, so a stale
     * snapshot from another device (lower total) is ignored instead of overwriting newer progress.
     */
    public function sync(Customer $customer, Dhikr $dhikr, int $currentCount, int $totalCount): DhikrProgress
    {
        return DB::transaction(function () use ($customer, $dhikr, $currentCount, $totalCount): DhikrProgress {
            $progress = DhikrProgress::query()
                ->where('customer_id', $customer->id)
                ->where('dhikr_id', $dhikr->id)
                ->lockForUpdate()
                ->first()
                ?? new DhikrProgress(['customer_id' => $customer->id, 'dhikr_id' => $dhikr->id]);

            if ($progress->exists && $totalCount < $progress->total_count) {
                return $progress;
            }

            $progress->fill([
                'current_count' => $currentCount,
                'total_count' => $totalCount,
                'completed_cycles' => intdiv($totalCount, max(1, (int) $dhikr->target_count)),
                'last_counted_at' => now(),
            ])->save();

            return $progress;
        });
    }

    /**
     * @return array{current_count: int, total_count: int, completed_cycles: int, last_counted_at: ?string}
     */
    public static function present(?DhikrProgress $progress): array
    {
        return [
            'current_count' => $progress?->current_count ?? 0,
            'total_count' => $progress?->total_count ?? 0,
            'completed_cycles' => $progress?->completed_cycles ?? 0,
            'last_counted_at' => $progress?->last_counted_at?->toIso8601String(),
        ];
    }
}
