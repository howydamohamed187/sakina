<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->string('status', 16)->default('open')->after('title');
            $table->timestamp('opened_at')->nullable()->after('status');
            $table->timestamp('closed_at')->nullable()->after('opened_at');

            $table->index(['customer_id', 'status']);
        });

        DB::table('ai_conversations')->whereNull('opened_at')->update(['opened_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'status']);
            $table->dropColumn(['status', 'opened_at', 'closed_at']);
        });
    }
};
