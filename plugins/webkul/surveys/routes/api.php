<?php

use Illuminate\Support\Facades\Route;
use Webkul\Survey\Http\Controllers\API\V1\PublicSurveyController;
use Webkul\Survey\Http\Controllers\API\V1\SurveyController;

Route::name('admin.api.v1.surveys.')
    ->prefix('admin/api/v1/surveys')
    ->middleware(['auth:sanctum'])
    ->group(function (): void {
        Route::softDeletableApiResource('surveys', SurveyController::class);
    });

Route::name('api.v1.public.surveys.')
    ->prefix('api/v1/public/surveys')
    ->group(function (): void {
        Route::get('{token}', [PublicSurveyController::class, 'show'])->name('show');
        Route::post('{token}/responses', [PublicSurveyController::class, 'store'])->name('responses.store');
    });
