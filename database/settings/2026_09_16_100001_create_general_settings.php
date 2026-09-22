<?php

use App\Settings\GeneralSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (GeneralSettings::defaults() as $name => $value) {
            $this->migrator->add('general.'.$name, $value);
        }
    }
};
