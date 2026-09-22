<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('third_party.google_map_key', null, true);
        $this->migrator->add('third_party.project_name', null);
        $this->migrator->add('third_party.firebase_file', null);
        $this->migrator->add('third_party.tranportal_id', null, true);
        $this->migrator->add('third_party.tranportal_password', null, true);
        $this->migrator->add('third_party.resource_key', null, true);
        $this->migrator->add('third_party.url', null);
        $this->migrator->add('third_party.is_test', true);
    }
};
