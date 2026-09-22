<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dhikrs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('category');
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dhikr_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dhikr_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['dhikr_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dhikr_favorites');
        Schema::dropIfExists('dhikrs');
    }
};
