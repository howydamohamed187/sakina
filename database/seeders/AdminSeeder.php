<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sakina.test'],
            [
                'name' => 'مدير سكينة',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ],
        );

        $admin->assignRole('super_admin');
    }
}
