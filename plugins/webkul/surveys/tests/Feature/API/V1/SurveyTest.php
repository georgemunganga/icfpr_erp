<?php

use Webkul\Project\Models\Project;
use Webkul\Project\Models\Task;
use Webkul\Security\Enums\PermissionType;
use Webkul\Security\Models\User;
use Webkul\Survey\Enums\SurveyStatus;
use Webkul\Survey\Models\Survey;

require_once __DIR__.'/../../../../../support/tests/Helpers/SecurityHelper.php';
require_once __DIR__.'/../../../../../support/tests/Helpers/TestBootstrapHelper.php';

beforeEach(function () {
    TestBootstrapHelper::ensurePluginInstalled('projects');
    TestBootstrapHelper::ensurePluginInstalled('surveys');
    SecurityHelper::disableUserEvents();
});

afterEach(fn () => SecurityHelper::restoreUserEvents());

function actingAsSurveyApiUser(array $permissions = []): User
{
    $user = SecurityHelper::authenticateWithPermissions($permissions);

    $user->forceFill([
        'resource_permission' => PermissionType::GLOBAL,
    ])->saveQuietly();

    return $user;
}

function surveyApiRoute(string $action, mixed $survey = null): string
{
    $name = "admin.api.v1.surveys.surveys.{$action}";

    return $survey ? route($name, $survey) : route($name);
}

it('creates a survey through the admin api', function () {
    actingAsSurveyApiUser(['create_survey_survey']);

    $payload = [
        'title'       => 'Policy Research Intake',
        'description' => 'Initial field survey.',
        'status'      => SurveyStatus::Draft->value,
        'is_public'   => true,
    ];

    $this->postJson(surveyApiRoute('store'), $payload)
        ->assertCreated()
        ->assertJsonPath('message', 'Survey created successfully.')
        ->assertJsonPath('data.title', 'Policy Research Intake');

    $this->assertDatabaseHas('surveys_surveys', [
        'title' => 'Policy Research Intake',
    ]);
});

it('requires the linked task to belong to the selected project', function () {
    actingAsSurveyApiUser(['create_survey_survey']);

    $project = Project::factory()->create();
    $otherProject = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $otherProject->id,
    ]);

    $payload = [
        'title'      => 'Mismatched Link Survey',
        'status'     => SurveyStatus::Draft->value,
        'project_id' => $project->id,
        'task_id'    => $task->id,
    ];

    $this->postJson(surveyApiRoute('store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['task_id']);
});

it('returns the public survey schema', function () {
    $survey = Survey::create([
        'title'     => 'Public Survey',
        'status'    => SurveyStatus::Published->value,
        'is_public' => true,
    ]);

    $survey->questions()->create([
        'prompt'      => 'What is your role?',
        'type'        => 'short_text',
        'is_required' => true,
        'sort'        => 1,
    ]);

    $this->getJson(route('api.v1.public.surveys.show', $survey->public_token))
        ->assertOk()
        ->assertJsonPath('data.title', 'Public Survey')
        ->assertJsonPath('data.questions.0.type', 'short_text');
});

it('stores a public survey submission', function () {
    $survey = Survey::create([
        'title'     => 'Submission Survey',
        'status'    => SurveyStatus::Published->value,
        'is_public' => true,
    ]);

    $question = $survey->questions()->create([
        'prompt'      => 'How many meetings did you hold?',
        'type'        => 'number',
        'is_required' => true,
        'sort'        => 1,
    ]);

    $payload = [
        'respondent_name' => 'Field Officer',
        'answers'         => [
            $question->id => 4,
        ],
    ];

    $this->postJson(route('api.v1.public.surveys.responses.store', $survey->public_token), $payload)
        ->assertCreated()
        ->assertJsonPath('message', 'Survey response submitted successfully.');

    $this->assertDatabaseHas('surveys_responses', [
        'survey_id'       => $survey->id,
        'respondent_name' => 'Field Officer',
    ]);

    $this->assertDatabaseHas('surveys_answers', [
        'question_id' => $question->id,
        'value_text'  => '4',
    ]);
});
