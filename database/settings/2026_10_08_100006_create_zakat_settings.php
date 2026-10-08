<?php

use App\Settings\ZakatSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (ZakatSettings::defaults() as $key => $value) {
            $this->migrator->add("zakat.{$key}", $value);
        }
    }
};
