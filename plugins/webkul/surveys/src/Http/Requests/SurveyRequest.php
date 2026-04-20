<?php

namespace Webkul\Survey\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webkul\Project\Models\Task;
use Webkul\Survey\Enums\SurveyStatus;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $requiredRule = $isUpdate ? ['sometimes', 'required'] : ['required'];
        $surveyId = $this->route('survey');
        $projectId = $this->input('project_id');

        return [
            'title'       => [...$requiredRule, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('surveys_surveys', 'slug')->ignore($surveyId)],
            'status'      => [...$requiredRule, Rule::enum(SurveyStatus::class)],
            'is_public'   => ['nullable', 'boolean'],
            'opens_at'    => ['nullable', 'date'],
            'closes_at'   => ['nullable', 'date', 'after_or_equal:opens_at'],
            'company_id'  => ['nullable', 'integer', 'exists:companies,id'],
            'project_id'  => ['nullable', 'integer', 'exists:projects_projects,id'],
            'task_id'     => [
                'nullable',
                'integer',
                Rule::exists(Task::class, 'id')->where(function ($query) use ($projectId) {
                    if (filled($projectId)) {
                        $query->where('project_id', $projectId);
                    }
                }),
            ],
        ];
    }
}
