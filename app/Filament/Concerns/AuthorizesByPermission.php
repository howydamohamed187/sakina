<?php

namespace App\Filament\Concerns;

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesByPermission
{
    public static function canViewAny(): bool
    {
        return static::authorized(Permissions::ACTION_VIEW);
    }

    public static function canCreate(): bool
    {
        return static::authorized(Permissions::ACTION_CREATE);
    }

    public static function canEdit(Model $record): bool
    {
        return static::authorized(Permissions::ACTION_UPDATE);
    }

    public static function canDelete(Model $record): bool
    {
        return static::authorized(Permissions::ACTION_DELETE);
    }

    public static function canDeleteAny(): bool
    {
        return static::authorized(Permissions::ACTION_DELETE);
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::authorized(Permissions::ACTION_DELETE);
    }

    public static function canForceDeleteAny(): bool
    {
        return static::authorized(Permissions::ACTION_DELETE);
    }

    public static function canRestore(Model $record): bool
    {
        return static::authorized(Permissions::ACTION_RESTORE);
    }

    public static function canRestoreAny(): bool
    {
        return static::authorized(Permissions::ACTION_RESTORE);
    }

    public static function canReorder(): bool
    {
        return static::authorized(Permissions::ACTION_UPDATE);
    }

    protected static function authorized(string $action): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }

        $resource = static::$permissionResource ?? null;

        return filled($resource) && $user->can(Permissions::name($resource, $action));
    }
}
