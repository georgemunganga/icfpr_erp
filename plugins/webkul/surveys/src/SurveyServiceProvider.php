<?php

namespace Webkul\Survey;

use Filament\Panel;
use Webkul\PluginManager\Console\Commands\InstallCommand;
use Webkul\PluginManager\Console\Commands\UninstallCommand;
use Webkul\PluginManager\Package;
use Webkul\PluginManager\PackageServiceProvider;

class SurveyServiceProvider extends PackageServiceProvider
{
    public static string $name = 'surveys';

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasRoutes(['web', 'api'])
            ->hasMigrations([
                '2026_04_20_000001_create_surveys_surveys_table',
                '2026_04_20_000002_create_surveys_questions_table',
                '2026_04_20_000003_create_surveys_responses_table',
                '2026_04_20_000004_create_surveys_answers_table',
                '2026_04_20_000005_add_field_metadata_to_surveys_questions_table',
                '2026_04_20_000006_add_project_and_task_to_surveys_surveys_table',
            ])
            ->runsMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->runsMigrations();
            })
            ->hasUninstallCommand(function (UninstallCommand $command) {});
    }

    public function packageBooted(): void
    {
        //
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(SurveyPlugin::make());
        });
    }
}
