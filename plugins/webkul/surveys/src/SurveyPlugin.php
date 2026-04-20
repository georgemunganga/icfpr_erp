<?php

namespace Webkul\Survey;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Webkul\PluginManager\Package;

class SurveyPlugin implements Plugin
{
    public function getId(): string
    {
        return 'surveys';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        if (! Package::isPluginInstalled($this->getId())) {
            return;
        }

        $panel->when($panel->getId() === 'admin', function (Panel $panel): void {
            $panel
                ->discoverResources(
                    in: __DIR__.'/Filament/Resources',
                    for: 'Webkul\\Survey\\Filament\\Resources'
                )
                ->discoverPages(
                    in: __DIR__.'/Filament/Pages',
                    for: 'Webkul\\Survey\\Filament\\Pages'
                )
                ->discoverWidgets(
                    in: __DIR__.'/Filament/Widgets',
                    for: 'Webkul\\Survey\\Filament\\Widgets'
                );
        });
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
