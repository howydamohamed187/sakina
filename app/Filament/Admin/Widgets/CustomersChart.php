<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class CustomersChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '260px';

    protected static string $color = 'success';

    public function getHeading(): string
    {
        return __('app.dashboard_widgets.customers_chart');
    }

    public function getDescription(): ?string
    {
        return __('app.dashboard_widgets.customers_chart_hint');
    }

    protected function getData(): array
    {
        $series = DashboardMetrics::series(Customer::class);

        return [
            'datasets' => [
                [
                    'label' => __('app.dashboard_widgets.customers'),
                    'data' => $series['data'],
                    'fill' => 'start',
                    'borderColor' => '#0f766e',
                    'backgroundColor' => 'rgba(15, 118, 110, 0.18)',
                    'tension' => 0.35,
                    'pointRadius' => 3,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
