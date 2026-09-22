<?php

namespace App\Support;

use Spatie\Permission\Models\Role;

class Roles
{
    public const SUPER_ADMIN = 'super_admin';

    public const ADMIN = 'admin';

    public const CUSTOMER = 'customer';

    public static function staff(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN];
    }

    public static function system(): array
    {
        return [self::SUPER_ADMIN, self::ADMIN, self::CUSTOMER];
    }

    public static function isCustomer(string $role): bool
    {
        return $role === self::CUSTOMER;
    }

    public static function typeLabel(string $role): string
    {
        return self::isCustomer($role)
            ? __('app.role_types.app')
            : __('app.role_types.panel');
    }

    public static function ensure(string $name): Role
    {
        return Role::findOrCreate($name, 'web');
    }

    public static function label(string $role): string
    {
        $key = 'app.role_names.'.$role;

        return trans()->has($key) ? __($key) : $role;
    }
}
