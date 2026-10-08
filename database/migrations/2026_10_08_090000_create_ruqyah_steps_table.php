<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruqyah_steps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('instruction')->nullable();
            $table->longText('content');
            $table->unsignedSmallInteger('repeat_count')->default(1);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruqyah_steps');
    }
};
