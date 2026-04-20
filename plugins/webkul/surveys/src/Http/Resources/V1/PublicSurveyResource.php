<?php

namespace Webkul\Survey\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicSurveyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title'       => $this->title,
            'description' => $this->description,
            'slug'        => $this->slug,
            'public_token'=> $this->public_token,
            'opens_at'    => $this->opens_at,
            'closes_at'   => $this->closes_at,
            'questions'   => SurveyQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
