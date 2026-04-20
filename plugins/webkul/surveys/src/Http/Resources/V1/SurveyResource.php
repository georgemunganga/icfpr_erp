<?php

namespace Webkul\Survey\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Project\Http\Resources\V1\ProjectResource as ApiProjectResource;
use Webkul\Project\Http\Resources\V1\TaskResource as ApiTaskResource;
use Webkul\Security\Http\Resources\V1\UserResource;
use Webkul\Support\Http\Resources\V1\CompanyResource;

class SurveyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'slug'            => $this->slug,
            'public_token'    => $this->public_token,
            'status'          => $this->status,
            'is_public'       => (bool) $this->is_public,
            'opens_at'        => $this->opens_at,
            'closes_at'       => $this->closes_at,
            'company_id'      => $this->company_id,
            'project_id'      => $this->project_id,
            'task_id'         => $this->task_id,
            'creator_id'      => $this->creator_id,
            'questions_count' => $this->whenCounted('questions'),
            'responses_count' => $this->whenCounted('responses'),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'deleted_at'      => $this->deleted_at,
            'company'         => new CompanyResource($this->whenLoaded('company')),
            'project'         => new ApiProjectResource($this->whenLoaded('project')),
            'task'            => new ApiTaskResource($this->whenLoaded('task')),
            'creator'         => new UserResource($this->whenLoaded('creator')),
            'questions'       => SurveyQuestionResource::collection($this->whenLoaded('questions')),
            'responses'       => SurveyResponseResource::collection($this->whenLoaded('responses')),
        ];
    }
}
