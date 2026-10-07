<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_dhikr_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dhikr_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_count')->default(0);
            $table->unsignedBigInteger('total_count')->default(0);
            $table->unsignedInteger('completed_cycles')->default(0);
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'dhikr_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dhikr_progress');
    }
};
