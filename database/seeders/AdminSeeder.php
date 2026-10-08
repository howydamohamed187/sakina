<?php

namespace Database\Seeders;

use App\Models\User;
use App\Notifications\LocalizedNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Set when the admin was created with a generated password, so app:install can print it.
     */
    public static ?string $generatedPassword = null;

    public function run(): void
    {
        $password = config('install.admin_password');

        if (blank($password)) {
            $password = app()->environment('local', 'testing') ? 'password' : Str::password(16, symbols: false);
        }

        $admin = User::query()->where('email', config('install.admin_email'))->first();

        if (! $admin) {
            $admin = User::query()->create([
                'email' => config('install.admin_email'),
                'name' => config('install.admin_name'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => 'active',
                'locale' => 'ar',
                'theme' => 'system',
            ]);

            if (blank(config('install.admin_password'))) {
                self::$generatedPassword = $password;
            }
        }

        $admin->assignRole('super_admin');

        if ($admin->notifications()->count() === 0) {
            $admin->notify(new LocalizedNotification('welcome'));
        }
    }
}
