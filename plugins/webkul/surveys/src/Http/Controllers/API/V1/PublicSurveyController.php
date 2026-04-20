<?php

namespace Webkul\Survey\Http\Controllers\API\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as ValidatorContract;
use Webkul\Survey\Enums\SurveyQuestionType;
use Webkul\Survey\Http\Resources\V1\PublicAppBootstrapResource;
use Webkul\Survey\Http\Resources\V1\PublicSurveyListResource;
use Webkul\Survey\Http\Resources\V1\PublicSurveyResource;
use Webkul\Survey\Models\Survey;
use Webkul\Survey\Models\SurveyResponse;

class PublicSurveyController extends Controller
{
    public function bootstrap(): PublicAppBootstrapResource
    {
        return new PublicAppBootstrapResource([
            'surveys' => $this->publicSurveyQuery()->get(),
        ]);
    }

    public function index(): AnonymousResourceCollection
    {
        return PublicSurveyListResource::collection(
            $this->publicSurveyQuery()->get()
        );
    }

    public function show(string $token): PublicSurveyResource
    {
        $survey = Survey::query()
            ->publiclyAccessible()
            ->with('questions')
            ->where('public_token', $token)
            ->firstOrFail();

        return new PublicSurveyResource($survey);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        $survey = $this->resolveSurvey($token);
        $validator = $this->makeValidator($request, $survey);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $response = $this->persistResponse($request, $survey, $validator->validated());

        return response()->json([
            'message'      => 'Survey response submitted successfully.',
            'response_id'  => $response->id,
            'submitted_at' => $response->submitted_at,
        ], 201);
    }

    public function resolveSurvey(string $token): Survey
    {
        $survey = Survey::query()
            ->with('questions')
            ->where('public_token', $token)
            ->firstOrFail();

        if (! $survey->isOpenForPublicSubmission()) {
            abort(403, 'This survey is not accepting responses.');
        }

        return $survey;
    }

    public function makeValidator(Request $request, Survey $survey): ValidatorContract
    {
        return Validator::make(
            $request->all(),
            $this->rules($survey),
            $this->messages($survey)
        );
    }

    public function persistResponse(Request $request, Survey $survey, array $validated): SurveyResponse
    {
        return DB::transaction(function () use ($request, $survey, $validated) {
            $response = $survey->responses()->create([
                'submitted_at'             => now(),
                'status'                   => 'submitted',
                'public_token'             => $request->route('token'),
                'session_key'              => $request->hasSession() ? $request->session()->getId() : null,
                'respondent_name'          => $validated['respondent_name'] ?? null,
                'respondent_email'         => $validated['respondent_email'] ?? null,
                'respondent_phone'         => $validated['respondent_phone'] ?? null,
                'respondent_organization'  => $validated['respondent_organization'] ?? null,
                'respondent_location'      => $validated['respondent_location'] ?? null,
            ]);

            foreach ($survey->questions as $question) {
                $answer = data_get($validated, "answers.{$question->id}");

                if ($question->type === SurveyQuestionType::MultipleChoice->value) {
                    $response->answers()->create([
                        'question_id' => $question->id,
                        'value_json'  => $answer ?: [],
                    ]);

                    continue;
                }

                $response->answers()->create([
                    'question_id' => $question->id,
                    'value_text'  => is_bool($answer) ? ($answer ? '1' : '0') : ($answer !== null ? (string) $answer : null),
                ]);
            }

            return $response;
        });
    }

    protected function rules(Survey $survey): array
    {
        $rules = [
            'respondent_name'         => ['nullable', 'string', 'max:255'],
            'respondent_email'        => ['nullable', 'email', 'max:255'],
            'respondent_phone'        => ['nullable', 'string', 'max:255'],
            'respondent_organization' => ['nullable', 'string', 'max:255'],
            'respondent_location'     => ['nullable', 'string', 'max:255'],
            'answers'                 => ['required', 'array'],
        ];

        foreach ($survey->questions as $question) {
            $key = "answers.{$question->id}";
            $required = $question->is_required ? ['required'] : ['nullable'];
            $optionValues = $question->getOptionValues();
            $min = data_get($question->settings, 'min');
            $max = data_get($question->settings, 'max');
            $pattern = data_get($question->settings, 'pattern');

            $rules[$key] = match ($question->type) {
                SurveyQuestionType::ShortText->value,
                SurveyQuestionType::LongText->value       => [...$required, 'string'],
                SurveyQuestionType::Email->value          => [...$required, 'email'],
                SurveyQuestionType::Phone->value          => [...$required, 'string'],
                SurveyQuestionType::Url->value            => [...$required, 'url'],
                SurveyQuestionType::Number->value         => [...$required, 'numeric'],
                SurveyQuestionType::Date->value           => [...$required, 'date'],
                SurveyQuestionType::SingleChoice->value,
                SurveyQuestionType::Select->value         => [...$required, 'string', ...($optionValues ? [Rule::in($optionValues)] : [])],
                SurveyQuestionType::MultipleChoice->value => [...$required, 'array'],
                SurveyQuestionType::YesNo->value          => [...$required, 'boolean'],
                SurveyQuestionType::Rating->value         => [...$required, 'numeric'],
                default                                   => [...$required],
            };

            if ($question->type === SurveyQuestionType::MultipleChoice->value) {
                $rules["{$key}.*"] = ['string', ...($optionValues ? [Rule::in($optionValues)] : [])];
            }

            if (in_array($question->type, [
                SurveyQuestionType::ShortText->value,
                SurveyQuestionType::LongText->value,
                SurveyQuestionType::Phone->value,
            ], true)) {
                if (filled($min)) {
                    $rules[$key][] = 'min:'.$min;
                }

                if (filled($max)) {
                    $rules[$key][] = 'max:'.$max;
                }

                if (filled($pattern)) {
                    $rules[$key][] = 'regex:'.$pattern;
                }
            }

            if (in_array($question->type, [
                SurveyQuestionType::Number->value,
                SurveyQuestionType::Rating->value,
            ], true)) {
                if (filled($min)) {
                    $rules[$key][] = 'min:'.$min;
                }

                if (filled($max)) {
                    $rules[$key][] = 'max:'.$max;
                }
            }
        }

        return $rules;
    }

    protected function messages(Survey $survey): array
    {
        $messages = [];

        foreach ($survey->questions as $question) {
            $messages["answers.{$question->id}.required"] = $question->prompt.' is required.';
        }

        return $messages;
    }

    protected function publicSurveyQuery(): Builder
    {
        return Survey::query()
            ->publiclyAccessible()
            ->withCount('questions')
            ->orderBy('title');
    }
}
