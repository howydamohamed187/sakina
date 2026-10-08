<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_prayer_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('prayer', 16);
            $table->boolean('enabled')->default(true);
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('vibration_enabled')->default(true);
            $table->timestamps();

            $table->unique(['customer_id', 'prayer'], 'customer_prayer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_prayer_notification_settings');
    }
};
