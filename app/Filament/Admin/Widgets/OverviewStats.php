<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\DailyQuestion;
use App\Models\DailyQuestionAnswer;
use App\Models\Dhikr;
use App\Models\Dua;
use App\Models\Hadith;
use App\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            $this->stat(
                __('app.dashboard_widgets.customers'),
                Customer::query()->count(),
                Customer::class,
                'heroicon-o-user-group',
                'success',
            ),
            $this->stat(
                __('app.dashboard_widgets.questions'),
                DailyQuestion::query()->count(),
                DailyQuestion::class,
                'heroicon-o-light-bulb',
                'warning',
            ),
            $this->stat(
                __('app.dashboard_widgets.answers'),
                DailyQuestionAnswer::query()->count(),
                DailyQuestionAnswer::class,
                'heroicon-o-chat-bubble-left-right',
                'info',
                'answered_at',
            ),
            $this->stat(
                __('app.dashboard_widgets.hadiths'),
                Hadith::query()->count(),
                Hadith::class,
                'heroicon-o-book-open',
                'primary',
            ),
            $this->stat(
                __('app.dashboard_widgets.adhkar'),
                Dhikr::query()->count(),
                Dhikr::class,
                'heroicon-o-sparkles',
                'success',
            ),
            $this->stat(
                __('app.dashboard_widgets.duas'),
                Dua::query()->count(),
                Dua::class,
                'heroicon-o-heart',
                'danger',
            ),
        ];
    }

    /**
     * @param  class-string  $model
     */
    protected function stat(
        string $label,
        int $value,
        string $model,
        string $icon,
        string $color,
        string $column = 'created_at',
    ): Stat {
        $week = DashboardMetrics::thisWeekCount($model, $column);

        return Stat::make($label, number_format($value))
            ->icon($icon)
            ->chart(DashboardMetrics::sparkline($model, 7, $column))
            ->chartColor($color)
            ->color($color)
            ->description(__('app.dashboard_widgets.new_this_week', ['count' => $week]))
            ->descriptionIcon($week > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
            ->descriptionColor($week > 0 ? 'success' : 'gray');
    }
}
