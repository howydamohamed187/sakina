<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_questions', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('daily_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_question_id')->constrained()->cascadeOnDelete();
            $table->string('body');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('daily_question_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('shown_on');
            $table->unsignedInteger('cycle')->default(1);
            $table->timestamps();

            $table->unique(['customer_id', 'shown_on']);
            $table->index(['customer_id', 'cycle']);
        });

        Schema::create('daily_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('daily_question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('daily_question_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('option_body');
            $table->boolean('is_correct')->default(false);
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->unique('daily_question_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_question_answers');
        Schema::dropIfExists('daily_question_assignments');
        Schema::dropIfExists('daily_question_options');
        Schema::dropIfExists('daily_questions');
    }
};
