<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys_surveys')->cascadeOnDelete();
            $table->text('prompt');
            $table->text('help_text')->nullable();
            $table->string('type');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys_questions');
    }
};
