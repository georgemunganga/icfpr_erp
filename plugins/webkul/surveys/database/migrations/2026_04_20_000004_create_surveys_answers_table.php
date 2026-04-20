<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('response_id')->constrained('surveys_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('surveys_questions')->cascadeOnDelete();
            $table->longText('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys_answers');
    }
};
