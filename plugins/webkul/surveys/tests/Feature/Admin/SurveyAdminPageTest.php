<?php

use Illuminate\Support\Facades\Auth;
use Webkul\Security\Enums\PermissionType;
use Webkul\Security\Models\User;
use Webkul\Survey\Enums\SurveyStatus;
use Webkul\Survey\Models\Survey;

require_once __DIR__.'/../../../../support/tests/Helpers/SecurityHelper.php';
require_once __DIR__.'/../../../../support/tests/Helpers/TestBootstrapHelper.php';

beforeEach(function () {
    TestBootstrapHelper::ensurePluginInstalled('surveys');
    SecurityHelper::disableUserEvents();
});

afterEach(fn () => SecurityHelper::restoreUserEvents());

function makeSurveyAdminUser(array $permissions): User
{
    $user = SecurityHelper::authenticateWithPermissions($permissions);

    $user->forceFill([
        'resource_permission' => PermissionType::GLOBAL,
        'email_verified_at'   => now(),
    ])->saveQuietly();

    Auth::shouldUse('web');

    return $user;
}

it('loads the survey create page', function () {
    $user = makeSurveyAdminUser(['create_survey_survey']);

    $this->actingAs($user, 'web')
        ->get(route('filament.admin.resources.research.surveys.create'))
        ->assertOk();
});

it('loads the survey view page', function () {
    $user = makeSurveyAdminUser([
        'view_any_survey_survey',
        'view_survey_survey',
    ]);

    $survey = Survey::create([
        'title'     => 'Admin Survey View',
        'status'    => SurveyStatus::Draft->value,
        'is_public' => false,
    ]);

    $this->actingAs($user, 'web')
        ->get(route('filament.admin.resources.research.surveys.view', ['record' => $survey]))
        ->assertOk();
});
