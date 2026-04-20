<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys_questions', function (Blueprint $table): void {
            $table->string('placeholder')->nullable()->after('help_text');
            $table->json('settings')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('surveys_questions', function (Blueprint $table): void {
            $table->dropColumn(['placeholder', 'settings']);
        });
    }
};
