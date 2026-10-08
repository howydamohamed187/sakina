<?php

use App\Settings\RuqyahSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (RuqyahSettings::defaults() as $key => $value) {
            $this->migrator->add("ruqyah.{$key}", $value);
        }
    }
};
