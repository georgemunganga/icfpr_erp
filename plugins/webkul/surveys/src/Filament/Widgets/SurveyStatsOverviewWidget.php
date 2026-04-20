<?php

namespace Webkul\Survey\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Webkul\Survey\Models\Survey;
use Webkul\Survey\Models\SurveyResponse;

class SurveyStatsOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalSurveys = Survey::query()->count();
        $totalResponses = SurveyResponse::query()->count();
        $lastResponse = SurveyResponse::query()->latest('submitted_at')->first();

        return [
            Stat::make('Total surveys', $totalSurveys),
            Stat::make('Total responses', $totalResponses),
            Stat::make('Latest submission', $lastResponse?->submitted_at?->diffForHumans() ?? 'None yet'),
        ];
    }
}
