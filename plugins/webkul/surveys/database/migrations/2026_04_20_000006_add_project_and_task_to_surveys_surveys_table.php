<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys_surveys', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_id')->nullable()->after('company_id');
            $table->unsignedBigInteger('task_id')->nullable()->after('project_id');
        });

        if (Schema::hasTable('projects_projects')) {
            Schema::table('surveys_surveys', function (Blueprint $table): void {
                $table->foreign('project_id')
                    ->references('id')
                    ->on('projects_projects')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('projects_tasks')) {
            Schema::table('surveys_surveys', function (Blueprint $table): void {
                $table->foreign('task_id')
                    ->references('id')
                    ->on('projects_tasks')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('surveys_surveys', function (Blueprint $table): void {
            try {
                $table->dropForeign(['task_id']);
            } catch (Throwable) {
            }

            try {
                $table->dropForeign(['project_id']);
            } catch (Throwable) {
            }

            $table->dropColumn(['task_id', 'project_id']);
        });
    }
};
