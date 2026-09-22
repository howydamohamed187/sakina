<?php

namespace App\Filament\Admin\Widgets;

use App\Models\DailyQuestionAnswer;
use App\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class QuestionAnswersChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '260px';

    protected static string $color = 'warning';

    public function getHeading(): string
    {
        return __('app.dashboard_widgets.answers_chart');
    }

    public function getDescription(): ?string
    {
        return __('app.dashboard_widgets.answers_chart_hint');
    }

    protected function getData(): array
    {
        $series = DashboardMetrics::series(DailyQuestionAnswer::class, 14, 'answered_at');

        return [
            'datasets' => [
                [
                    'label' => __('app.dashboard_widgets.answers'),
                    'data' => $series['data'],
                    'backgroundColor' => 'rgba(245, 158, 11, 0.75)',
                    'borderColor' => '#d97706',
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
