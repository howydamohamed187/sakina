<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\CustomersChart;
use App\Filament\Admin\Widgets\LatestCustomers;
use App\Filament\Admin\Widgets\LatestDhikrs;
use App\Filament\Admin\Widgets\LatestQuestions;
use App\Filament\Admin\Widgets\OverviewStats;
use App\Filament\Admin\Widgets\QuestionAnswersChart;
use App\Filament\Admin\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 0;

    public function getTitle(): string
    {
        return __('app.dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.home');
    }

    public function getWidgets(): array
    {
        return [
            WelcomeWidget::class,
            OverviewStats::class,
            CustomersChart::class,
            QuestionAnswersChart::class,
            LatestCustomers::class,
            LatestQuestions::class,
            LatestDhikrs::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
