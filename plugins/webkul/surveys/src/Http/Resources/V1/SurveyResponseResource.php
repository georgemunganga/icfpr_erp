<?php

namespace Webkul\Survey\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'status'                  => $this->status,
            'submitted_at'            => $this->submitted_at,
            'respondent_name'         => $this->respondent_name,
            'respondent_email'        => $this->respondent_email,
            'respondent_phone'        => $this->respondent_phone,
            'respondent_organization' => $this->respondent_organization,
            'respondent_location'     => $this->respondent_location,
            'answers'                 => $this->whenLoaded('answers', function () {
                return $this->answers->map(function ($answer) {
                    return [
                        'question_id' => $answer->question_id,
                        'value_text'  => $answer->value_text,
                        'value_json'  => $answer->value_json,
                    ];
                });
            }),
        ];
    }
}
