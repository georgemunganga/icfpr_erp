<?php

namespace Webkul\Survey\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicAppBootstrapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'branding' => [
                'app_name'      => config('app.name'),
                'logo_url'      => config('branding.logo_url'),
                'primary_color' => config('branding.primary_color'),
            ],
            'surveys' => PublicSurveyListResource::collection($this->resource['surveys']),
        ];
    }
}
