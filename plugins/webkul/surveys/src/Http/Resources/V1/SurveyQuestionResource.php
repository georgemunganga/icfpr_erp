<?php

namespace Webkul\Survey\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Survey\Enums\SurveyQuestionType;

class SurveyQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = SurveyQuestionType::from($this->type);

        return [
            'id'               => $this->id,
            'prompt'           => $this->prompt,
            'help_text'        => $this->help_text,
            'placeholder'      => $this->placeholder,
            'type'             => $this->type,
            'input_type'       => $type->inputType(),
            'answer_format'    => $type->answerFormat(),
            'accepts_multiple' => $type->acceptsMultipleAnswers(),
            'is_required'      => (bool) $this->is_required,
            'sort'             => $this->sort,
            'options'          => $this->options ?? [],
            'settings'         => $this->settings ?? [],
            'validation'       => [
                'required' => (bool) $this->is_required,
                'min'      => data_get($this->settings, 'min'),
                'max'      => data_get($this->settings, 'max'),
                'step'     => data_get($this->settings, 'step'),
                'pattern'  => data_get($this->settings, 'pattern'),
            ],
        ];
    }
}
