<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_daily_message_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->unsignedInteger('reserved')->default(0);
            $table->timestamps();

            $table->unique(['customer_id', 'usage_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_daily_message_quotas');
    }
};
