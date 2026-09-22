<?php

use App\Support\Fonts;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('appearance.font_family', Fonts::DEFAULT_FAMILY);
        $this->migrator->add('appearance.font_size', Fonts::DEFAULT_SIZE);
    }
};
