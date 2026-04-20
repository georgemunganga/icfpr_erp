<?php

namespace Webkul\Survey\Filament\Resources\SurveyResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Webkul\Support\Traits\HasRecordNavigationTabs;
use Webkul\Survey\Filament\Resources\SurveyResource;

class ViewSurvey extends ViewRecord
{
    use HasRecordNavigationTabs;

    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
