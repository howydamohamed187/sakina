<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occasion_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('event_date');
            $table->foreignId('dua_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['customer_id', 'is_active']);
        });

        Schema::create('occasion_reminder_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('occasion_reminder_id')->constrained()->cascadeOnDelete();
            $table->date('occurrence_date');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['occasion_reminder_id', 'occurrence_date'], 'occasion_reminder_occurrence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occasion_reminder_notifications');
        Schema::dropIfExists('occasion_reminders');
    }
};
