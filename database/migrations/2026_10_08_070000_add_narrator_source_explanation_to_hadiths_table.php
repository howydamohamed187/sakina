<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hadiths', function (Blueprint $table) {
            $table->string('narrator')->nullable()->after('body');
            $table->string('source')->nullable()->after('narrator');
            $table->text('explanation')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('hadiths', function (Blueprint $table) {
            $table->dropColumn(['narrator', 'source', 'explanation']);
        });
    }
};
