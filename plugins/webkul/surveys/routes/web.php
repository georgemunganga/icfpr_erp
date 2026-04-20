<?php

use Illuminate\Support\Facades\Route;
use Webkul\Survey\Http\Controllers\PublicSurveyPageController;

Route::name('surveys.public.')
    ->prefix('surveys')
    ->middleware('web')
    ->group(function (): void {
        Route::get('{token}', [PublicSurveyPageController::class, 'show'])->name('show');
        Route::post('{token}', [PublicSurveyPageController::class, 'store'])->name('store');
    });
