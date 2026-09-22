<?php

namespace App\Filament\Concerns;

use App\Support\Permissions;
use App\Support\Roles;

trait AuthorizesSettingsPage
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }

        if (static::isSuperAdminOnly()) {
            return false;
        }

        return $user->can(Permissions::name(Permissions::RESOURCE_SETTINGS, Permissions::ACTION_UPDATE));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    protected static function isSuperAdminOnly(): bool
    {
        return false;
    }
}
