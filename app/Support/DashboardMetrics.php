<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class DashboardMetrics
{
    /**
     * @param  class-string  $model
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public static function series(string $model, int $days = 14, string $column = 'created_at'): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $rows = $model::query()
            ->where($column, '>=', $from)
            ->get([$column])
            ->groupBy(fn ($record) => Carbon::parse($record->{$column})->toDateString())
            ->map->count();

        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->format('d/m');
            $data[] = (int) ($rows[$day->toDateString()] ?? 0);
        }

        return compact('labels', 'data');
    }

    /**
     * @param  class-string  $model
     * @return array<int, int>
     */
    public static function sparkline(string $model, int $days = 7, string $column = 'created_at'): array
    {
        return self::series($model, $days, $column)['data'];
    }

    /**
     * @param  class-string  $model
     */
    public static function thisWeekCount(string $model, string $column = 'created_at'): int
    {
        return $model::query()->where($column, '>=', now()->startOfWeek())->count();
    }
}
