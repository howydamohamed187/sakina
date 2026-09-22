<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservation_codes') && ! Schema::hasTable('verification_codes')) {
            Schema::rename('reservation_codes', 'verification_codes');
        }
    }

    public function down(): void
    {
        //
    }
};
