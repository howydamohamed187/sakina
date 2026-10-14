<?php

use App\Settings\NormalizeEncryptedSettingPayloads;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        (new NormalizeEncryptedSettingPayloads)->up();
    }
};
