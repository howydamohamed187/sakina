<?php

namespace App\Filament\Forms;

use App\Filament\Forms\Components\PhoneField;

class PhoneInput
{
    public static function make(string $uniqueTable = 'users', string $uniqueColumn = 'phone', bool $required = true): PhoneField
    {
        return PhoneField::make('phone')
            ->label(__('app.fields.phone'))
            ->required($required)
            ->uniqueOn($uniqueTable, $uniqueColumn);
    }
}
