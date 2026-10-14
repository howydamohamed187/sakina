<?php

use App\Settings\StorePlainAiSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        (new StorePlainAiSettings)->up();
    }
};
