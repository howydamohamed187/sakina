<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dhikrs', function (Blueprint $table) {
            $table->text('description')->nullable()->after('body');
            $table->boolean('is_countable')->default(false)->after('category')->index();
            $table->unsignedInteger('target_count')->nullable()->after('is_countable');
        });
    }

    public function down(): void
    {
        Schema::table('dhikrs', function (Blueprint $table) {
            $table->dropIndex(['is_countable']);
            $table->dropColumn(['description', 'is_countable', 'target_count']);
        });
    }
};
