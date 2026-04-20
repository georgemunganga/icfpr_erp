<?php

namespace Webkul\Survey\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Webkul\Project\Models\Project;
use Webkul\Project\Models\Task;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;
use Webkul\Survey\Enums\SurveyStatus;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surveys_surveys';

    protected $fillable = [
        'company_id',
        'project_id',
        'task_id',
        'creator_id',
        'title',
        'description',
        'slug',
        'public_token',
        'status',
        'is_public',
        'opens_at',
        'closes_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'opens_at'  => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function scopePubliclyAccessible(Builder $query): Builder
    {
        return $query
            ->where('is_public', true)
            ->where('status', SurveyStatus::Published->value)
            ->where(function (Builder $query): void {
                $query->whereNull('opens_at')->orWhere('opens_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('closes_at')->orWhere('closes_at', '>=', now());
            });
    }

    public function isOpenForPublicSubmission(): bool
    {
        if (! $this->is_public || $this->status !== SurveyStatus::Published->value) {
            return false;
        }

        if ($this->opens_at && $this->opens_at->isFuture()) {
            return false;
        }

        if ($this->closes_at && $this->closes_at->isPast()) {
            return false;
        }

        return true;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Survey $survey): void {
            $survey->creator_id ??= Auth::id();
            $survey->public_token ??= (string) Str::ulid();
            $survey->slug = static::generateUniqueSlug($survey->title, $survey->slug);

            if (blank($survey->project_id)) {
                $survey->task_id = null;
            }
        });

        static::updating(function (Survey $survey): void {
            if ($survey->isDirty('title') || blank($survey->slug)) {
                $survey->slug = static::generateUniqueSlug($survey->title, $survey->slug, $survey->id);
            }

            if ($survey->isDirty('project_id') && blank($survey->project_id)) {
                $survey->task_id = null;
            }
        });
    }

    protected static function generateUniqueSlug(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $title);
        $baseSlug = filled($baseSlug) ? $baseSlug : Str::lower(Str::random(8));
        $candidate = $baseSlug;
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }
}
