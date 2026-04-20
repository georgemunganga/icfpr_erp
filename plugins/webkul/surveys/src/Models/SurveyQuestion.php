<?php

namespace Webkul\Survey\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $table = 'surveys_questions';

    protected $fillable = [
        'survey_id',
        'prompt',
        'help_text',
        'placeholder',
        'type',
        'is_required',
        'sort',
        'options',
        'settings',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options'     => 'array',
        'settings'    => 'array',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'question_id');
    }

    public function getOptionValues(): array
    {
        return collect($this->options ?? [])
            ->map(fn (array $option) => (string) ($option['value'] ?? $option['label'] ?? ''))
            ->filter()
            ->values()
            ->all();
    }
}
