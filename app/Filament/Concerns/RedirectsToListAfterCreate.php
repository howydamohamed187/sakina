<?php

namespace App\Filament\Concerns;

trait RedirectsToListAfterCreate
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }
}
