<?php

namespace App\Filament\Admin\Pages;

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
        ];
    }

    public function getColumns(): int|string|array
    {
        return 1;
    }
}
