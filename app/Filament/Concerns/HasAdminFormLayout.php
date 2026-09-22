<?php

namespace App\Filament\Concerns;

use Filament\Support\Enums\MaxWidth;

trait HasAdminFormLayout
{
    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }
}
