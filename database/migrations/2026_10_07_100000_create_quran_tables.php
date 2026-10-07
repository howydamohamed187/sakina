<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors the Tanzil `quran_text` table from database/data/quran/quran-simple.sql. Read-only source data.
        Schema::create('quran_text', function (Blueprint $table) {
            $table->unsignedInteger('index')->primary();
            $table->unsignedSmallInteger('sura')->default(0);
            $table->unsignedSmallInteger('aya')->default(0);
            $table->text('text');

            $table->unique(['sura', 'aya']);
        });

        Schema::create('quran_surahs', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('name_en_translation')->nullable();
            $table->string('revelation_type', 16);
            $table->unsignedSmallInteger('ayahs_count');
            $table->json('information')->nullable();
            $table->timestamps();
        });

        Schema::create('quran_reciters', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('image')->nullable();
            $table->string('ayah_audio_url_template')->nullable();
            $table->string('surah_audio_url_template')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status', 16)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('quran_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sura');
            $table->unsignedSmallInteger('aya');
            $table->string('locale', 8);
            $table->text('text');
            $table->string('source')->nullable();
            $table->timestamps();

            $table->unique(['sura', 'aya', 'locale']);
        });

        Schema::create('quran_tafsirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sura');
            $table->unsignedSmallInteger('aya');
            $table->string('locale', 8);
            $table->longText('text');
            $table->string('source')->nullable();
            $table->timestamps();

            $table->unique(['sura', 'aya', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_tafsirs');
        Schema::dropIfExists('quran_translations');
        Schema::dropIfExists('quran_reciters');
        Schema::dropIfExists('quran_surahs');
        Schema::dropIfExists('quran_text');
    }
};
