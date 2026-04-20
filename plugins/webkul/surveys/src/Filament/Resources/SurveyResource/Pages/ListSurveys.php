<?php

namespace Webkul\Survey\Filament\Resources\SurveyResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\Survey\Filament\Resources\SurveyResource;
use Webkul\Survey\Filament\Widgets\SurveyStatsOverviewWidget;

class ListSurveys extends ListRecords
{
    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New survey')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SurveyStatsOverviewWidget::class,
        ];
    }
}
