<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permissions::all() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', Permissions::all())
            ->delete();

        $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
        $admin->syncPermissions(array_merge(
            [Permissions::ACCESS_ADMIN],
            Permissions::forResources([
                Permissions::RESOURCE_CUSTOMERS,
                Permissions::RESOURCE_CONTACTS,
                Permissions::RESOURCE_CONTACT_TYPES,
                Permissions::RESOURCE_DAILY_QUESTIONS,
                Permissions::RESOURCE_HADITHS,
                Permissions::RESOURCE_ADHKAR,
                Permissions::RESOURCE_DUAS,
            ]),
            Permissions::forResource(Permissions::RESOURCE_SETTINGS),
        ));

        Role::firstOrCreate(['name' => Roles::CUSTOMER, 'guard_name' => 'web']);

        Customer::query()->each(function (Customer $customer): void {
            if (! $customer->hasRole(Roles::CUSTOMER)) {
                $customer->assignRole(Roles::CUSTOMER);
            }
        });
    }
}
