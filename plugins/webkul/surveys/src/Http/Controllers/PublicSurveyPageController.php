<?php

namespace Webkul\Survey\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Webkul\Survey\Http\Controllers\API\V1\PublicSurveyController;
use Webkul\Survey\Models\Survey;

class PublicSurveyPageController extends Controller
{
    public function __construct(protected PublicSurveyController $publicSurveyController) {}

    public function show(string $token): View
    {
        $survey = Survey::query()
            ->publiclyAccessible()
            ->with('questions')
            ->where('public_token', $token)
            ->firstOrFail();

        return view('surveys::public.show', [
            'survey' => $survey,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $survey = $this->publicSurveyController->resolveSurvey($token);
        $validator = $this->publicSurveyController->makeValidator($request, $survey);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('surveys.public.show', $token));
        }

        $this->publicSurveyController->persistResponse($request, $survey, $validator->validated());

        return redirect()
            ->route('surveys.public.show', $token)
            ->with('success', 'Your response has been submitted.');
    }
}
