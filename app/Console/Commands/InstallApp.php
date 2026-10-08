<?php

namespace App\Console\Commands;

use App\Models\QuranSurah;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * First deployment: migrations, every seeder (roles, admin, settings, hadiths, adhkar, duas,
 * Quran text + surahs + reciters, ruqyah, zakat, daily questions), Quran text verification,
 * optional translations/tafsir download, and the storage link.
 */
class InstallApp extends Command
{
    protected $signature = 'app:install
        {--force : Run even if the app is already installed (settings seeders overwrite current values)}
        {--with-editions : Also download Quran translations and tafsir (needs internet, takes a while)}
        {--admin-email= : First admin email (default: ADMIN_EMAIL or admin@sakina.test)}
        {--admin-password= : First admin password (default: ADMIN_PASSWORD or a generated one)}';

    protected $description = 'Install the application on a new database: migrate and run all seeders';

    public function handle(): int
    {
        if ($this->isInstalled() && ! $this->option('force')) {
            $this->warn('The application is already installed (an admin user exists).');
            $this->line('Use --force to run all seeders again; this resets general, appearance and zakat settings to their defaults.');

            return self::FAILURE;
        }

        if (app()->isProduction() && $this->input->isInteractive() && ! $this->confirm('Install on the PRODUCTION database?', true)) {
            return self::FAILURE;
        }

        if ($email = $this->option('admin-email')) {
            config(['install.admin_email' => $email]);
        }

        if ($password = $this->option('admin-password')) {
            if (mb_strlen($password) < 8) {
                $this->error('The admin password must be at least 8 characters.');

                return self::FAILURE;
            }

            config(['install.admin_password' => $password]);
        }

        $steps = [
            'Running migrations' => fn (): int => $this->call('migrate', ['--force' => true]),
            'Running all seeders (this imports the full Quran text, may take a minute)' => fn (): int => $this->call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]),
            'Verifying the Quran text letter for letter against the source' => fn (): int => $this->call('quran:import', ['--verify' => true]),
        ];

        if ($this->option('with-editions')) {
            $steps['Downloading Quran translations and tafsir'] = fn (): int => $this->call('quran:sync-editions');
        }

        $steps['Linking public storage'] = fn (): int => is_link(public_path('storage')) || is_dir(public_path('storage'))
            ? self::SUCCESS
            : $this->call('storage:link');

        foreach ($steps as $label => $step) {
            $this->newLine();
            $this->components->info($label);

            try {
                $code = $step();
            } catch (Throwable $e) {
                $this->error($e->getMessage());
                $code = self::FAILURE;
            }

            if ($code !== self::SUCCESS) {
                $this->error("Install stopped at: {$label}");

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->components->info('Installation complete.');
        $this->components->twoColumnDetail('Quran surahs', (string) QuranSurah::query()->count());
        $this->components->twoColumnDetail('Admin email', (string) config('install.admin_email'));

        if (AdminSeeder::$generatedPassword !== null) {
            $this->components->twoColumnDetail('Admin password (generated, shown once)', AdminSeeder::$generatedPassword);
            $this->warn('Save this password now and change it after the first login.');
        }

        if (! $this->option('with-editions')) {
            $this->line('Translations and tafsir: run "php artisan quran:sync-editions" when the server has internet access.');
        }

        $this->line('Scheduler: add a cron entry for "php artisan schedule:run" every minute.');

        return self::SUCCESS;
    }

    private function isInstalled(): bool
    {
        try {
            return Schema::hasTable('users') && User::query()->exists();
        } catch (Throwable) {
            return false;
        }
    }
}
