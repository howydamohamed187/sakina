<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\DhikrResource;
use App\Models\Dhikr;
use App\Support\DhikrCategories;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestDhikrs extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.dashboard_widgets.latest_adhkar'))
            ->query(Dhikr::query()->latest())
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->recordUrl(function (Dhikr $record): ?string {
                return DhikrResource::canViewAny()
                    ? DhikrResource::getUrl('view', ['record' => $record])
                    : null;
            })
            ->columns([
                TextColumn::make('title')
                    ->label(__('app.fields.dhikr_title'))
                    ->limit(36),
                TextColumn::make('category')
                    ->label(__('app.fields.category'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => DhikrCategories::label($state)),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->since(),
            ]);
    }
}
