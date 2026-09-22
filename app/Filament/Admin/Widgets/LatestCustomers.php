<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCustomers extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.dashboard_widgets.latest_customers'))
            ->query(Customer::query()->latest())
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->recordUrl(function (Customer $record): ?string {
                return CustomerResource::canEdit($record)
                    ? CustomerResource::getUrl('edit', ['record' => $record])
                    : null;
            })
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->limit(28),
                TextColumn::make('email')
                    ->label(__('app.fields.email'))
                    ->limit(24),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->since(),
            ]);
    }
}
