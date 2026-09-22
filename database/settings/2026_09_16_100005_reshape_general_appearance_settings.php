<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if ($this->migrator->exists('general.app_name')) {
            $this->migrator->update('general.app_name', function ($value) {
                if (is_array($value)) {
                    return [
                        'ar' => (string) ($value['ar'] ?? 'سكينة'),
                        'en' => (string) ($value['en'] ?? 'Sakina'),
                    ];
                }

                $arabic = is_string($value) && $value !== '' ? $value : 'سكينة';

                return [
                    'ar' => $arabic,
                    'en' => $arabic === 'سكينة' ? 'Sakina' : $arabic,
                ];
            });
        }

        if ($this->migrator->exists('appearance.font_family')) {
            $this->migrator->update('appearance.font_family', fn () => 'Alexandria');
        }
    }
};
