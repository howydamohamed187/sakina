<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\WelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'لوحة التحكم';

    protected static ?string $navigationLabel = 'الرئيسية';

    protected static ?int $navigationSort = 0;

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
