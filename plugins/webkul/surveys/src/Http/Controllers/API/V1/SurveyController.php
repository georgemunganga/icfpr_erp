<?php

namespace Webkul\Survey\Http\Controllers\API\V1;

use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Webkul\Survey\Http\Requests\SurveyRequest;
use Webkul\Survey\Http\Resources\V1\SurveyResource;
use Webkul\Survey\Models\Survey;

class SurveyController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Survey::class);

        $surveys = QueryBuilder::for(Survey::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('is_public'),
                AllowedFilter::exact('company_id'),
                AllowedFilter::trashed(),
            ])
            ->allowedIncludes([
                'company',
                'project',
                'task',
                'creator',
                'questions',
                'responses',
            ])
            ->allowedSorts(['id', 'title', 'status', 'created_at', 'opens_at', 'closes_at'])
            ->withCount(['questions', 'responses'])
            ->paginate();

        return SurveyResource::collection($surveys);
    }

    public function store(SurveyRequest $request)
    {
        Gate::authorize('create', Survey::class);

        $survey = Survey::create($request->validated());

        return (new SurveyResource($survey->load(['company', 'project', 'task', 'creator'])->loadCount(['questions', 'responses'])))
            ->additional(['message' => 'Survey created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id)
    {
        $survey = QueryBuilder::for(Survey::where('id', $id))
            ->allowedIncludes([
                'company',
                'project',
                'task',
                'creator',
                'questions',
                'responses',
                'responses.answers',
            ])
            ->withCount(['questions', 'responses'])
            ->firstOrFail();

        Gate::authorize('view', $survey);

        return new SurveyResource($survey);
    }

    public function update(SurveyRequest $request, string $id)
    {
        $survey = Survey::findOrFail($id);

        Gate::authorize('update', $survey);

        $survey->update($request->validated());

        return (new SurveyResource($survey->load(['company', 'project', 'task', 'creator'])->loadCount(['questions', 'responses'])))
            ->additional(['message' => 'Survey updated successfully.']);
    }

    public function destroy(string $id)
    {
        $survey = Survey::findOrFail($id);

        Gate::authorize('delete', $survey);

        $survey->delete();

        return response()->json([
            'message' => 'Survey deleted successfully.',
        ]);
    }

    public function restore(string $id)
    {
        $survey = Survey::withTrashed()->findOrFail($id);

        Gate::authorize('restore', $survey);

        $survey->restore();

        return (new SurveyResource($survey->load(['company', 'project', 'task', 'creator'])->loadCount(['questions', 'responses'])))
            ->additional(['message' => 'Survey restored successfully.']);
    }

    public function forceDestroy(string $id)
    {
        $survey = Survey::withTrashed()->findOrFail($id);

        Gate::authorize('forceDelete', $survey);

        $survey->forceDelete();

        return response()->json([
            'message' => 'Survey permanently deleted.',
        ]);
    }
}
