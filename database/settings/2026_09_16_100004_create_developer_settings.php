<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('developer.debug_mode', false);
        $this->migrator->add('developer.otp_code_is_random', true);
    }
};
