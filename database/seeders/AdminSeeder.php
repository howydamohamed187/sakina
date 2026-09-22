<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\LocalizedNotification;
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
                'locale' => 'ar',
                'theme' => 'system',
            ],
        );

        $admin->assignRole('super_admin');

        if ($admin->notifications()->count() === 0) {
            $admin->notify(new LocalizedNotification('welcome'));
        }
    }
}
