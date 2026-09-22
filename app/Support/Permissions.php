<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Permissions
{
    public const ACCESS_ADMIN = 'access_admin';

    public const ACTION_VIEW = 'view';

    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    public const ACTION_RESTORE = 'restore';

    public const ACTIONS = [
        self::ACTION_VIEW,
        self::ACTION_CREATE,
        self::ACTION_UPDATE,
        self::ACTION_DELETE,
    ];

    public const GROUPS = [
        'create',
        'update',
        'delete',
        'restore',
        'other',
    ];

    public const RESOURCE_ADMINS = 'admins';

    public const RESOURCE_CUSTOMERS = 'customers';

    public const RESOURCE_CONTACTS = 'contacts';

    public const RESOURCE_CONTACT_TYPES = 'contact_types';

    public const RESOURCE_ROLES = 'roles';

    public const RESOURCE_DAILY_QUESTIONS = 'daily_questions';

    public const RESOURCE_HADITHS = 'hadiths';

    public const RESOURCE_ADHKAR = 'adhkar';

    public const RESOURCE_DUAS = 'duas';

    public const RESOURCE_SETTINGS = 'settings';

    public const RESOURCES = [
        self::RESOURCE_ADMINS,
        self::RESOURCE_CUSTOMERS,
        self::RESOURCE_CONTACTS,
        self::RESOURCE_CONTACT_TYPES,
        self::RESOURCE_ROLES,
        self::RESOURCE_DAILY_QUESTIONS,
        self::RESOURCE_HADITHS,
        self::RESOURCE_ADHKAR,
        self::RESOURCE_DUAS,
    ];

    public const TRASHABLE_RESOURCES = [
        self::RESOURCE_ADMINS,
        self::RESOURCE_CUSTOMERS,
        self::RESOURCE_CONTACTS,
        self::RESOURCE_CONTACT_TYPES,
        self::RESOURCE_DAILY_QUESTIONS,
        self::RESOURCE_HADITHS,
        self::RESOURCE_ADHKAR,
        self::RESOURCE_DUAS,
    ];

    public static function name(string $resource, string $action): string
    {
        return $resource.'.'.$action;
    }

    public static function actionsFor(string $resource): array
    {
        if ($resource === self::RESOURCE_SETTINGS) {
            return [self::ACTION_VIEW, self::ACTION_UPDATE];
        }

        $actions = self::ACTIONS;

        if (in_array($resource, self::TRASHABLE_RESOURCES, true)) {
            $actions[] = self::ACTION_RESTORE;
        }

        return $actions;
    }

    public static function forResource(string $resource): array
    {
        return array_map(
            fn (string $action): string => self::name($resource, $action),
            self::actionsFor($resource),
        );
    }

    public static function forResources(array $resources): array
    {
        return collect($resources)
            ->flatMap(fn (string $resource): array => self::forResource($resource))
            ->values()
            ->all();
    }

    public static function forAction(string $action, array $resources = self::RESOURCES): array
    {
        return array_map(
            fn (string $resource): string => self::name($resource, $action),
            $resources,
        );
    }

    public static function all(): array
    {
        return array_merge(
            [self::ACCESS_ADMIN],
            self::forResources(self::RESOURCES),
            self::forResource(self::RESOURCE_SETTINGS),
        );
    }

    public static function namesForGroup(string $group): array
    {
        return match ($group) {
            'create' => self::forAction(self::ACTION_CREATE),
            'update' => array_merge(
                self::forAction(self::ACTION_UPDATE),
                [self::name(self::RESOURCE_SETTINGS, self::ACTION_UPDATE)],
            ),
            'delete' => self::forAction(self::ACTION_DELETE),
            'restore' => self::forAction(self::ACTION_RESTORE, self::TRASHABLE_RESOURCES),
            'other' => array_merge(
                [self::ACCESS_ADMIN],
                self::forAction(self::ACTION_VIEW),
                [self::name(self::RESOURCE_SETTINGS, self::ACTION_VIEW)],
            ),
            default => [],
        };
    }

    public static function optionsForGroup(string $group): array
    {
        $names = self::namesForGroup($group);

        return Permission::query()
            ->whereIn('name', $names)
            ->get()
            ->sortBy(fn (Permission $permission) => array_search($permission->name, $names, true))
            ->mapWithKeys(fn (Permission $permission): array => [
                (string) $permission->getKey() => self::groupOptionLabel($permission->name, $group),
            ])
            ->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function groupedState(?Role $role): array
    {
        $assigned = collect($role?->permissions?->pluck('id', 'name') ?? [])
            ->map(fn ($id): string => (string) $id);

        return collect(self::GROUPS)
            ->mapWithKeys(fn (string $group): array => [
                $group => collect(self::namesForGroup($group))
                    ->map(fn (string $name): ?string => $assigned->get($name))
                    ->filter()
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $groups
     */
    public static function syncGroups(Role $role, array $groups): void
    {
        $ids = collect($groups)
            ->only(self::GROUPS)
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        $role->permissions()->sync($ids);
    }

    public static function label(string $permission): string
    {
        if ($permission === self::ACCESS_ADMIN) {
            return __('app.permission_names.access_admin');
        }

        [$resource, $action] = array_pad(explode('.', $permission, 2), 2, null);

        if (! $resource || ! $action) {
            $key = 'app.permission_names.'.$permission;

            return trans()->has($key) ? __($key) : $permission;
        }

        return __('app.permission_actions.'.$action).' '.__('app.permission_resources.'.$resource);
    }

    public static function groupOptionLabel(string $permission, string $group): string
    {
        if ($group === 'other') {
            return self::label($permission);
        }

        [$resource] = array_pad(explode('.', $permission, 2), 2, null);

        return __('app.permission_resources.'.$resource);
    }
}
