<?php

namespace Webkul\Survey\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Security\Models\User;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $table = 'surveys_responses';

    protected $fillable = [
        'survey_id',
        'submitted_by_user_id',
        'status',
        'submitted_at',
        'public_token',
        'session_key',
        'respondent_name',
        'respondent_email',
        'respondent_phone',
        'respondent_organization',
        'respondent_location',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'response_id');
    }
}
