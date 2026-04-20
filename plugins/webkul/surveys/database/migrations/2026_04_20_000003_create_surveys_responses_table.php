<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys_surveys')->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->string('public_token')->nullable();
            $table->string('session_key')->nullable();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_email')->nullable();
            $table->string('respondent_phone')->nullable();
            $table->string('respondent_organization')->nullable();
            $table->string('respondent_location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys_responses');
    }
};
