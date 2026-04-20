<?php

namespace Webkul\Survey\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\Security\Models\User;
use Webkul\Survey\Models\Survey;

class SurveyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_survey_survey');
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->can('view_survey_survey');
    }

    public function create(User $user): bool
    {
        return $user->can('create_survey_survey');
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->can('update_survey_survey');
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->can('delete_survey_survey');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_survey_survey');
    }

    public function restore(User $user, Survey $survey): bool
    {
        return $user->can('restore_survey_survey');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_survey_survey');
    }

    public function forceDelete(User $user, Survey $survey): bool
    {
        return $user->can('force_delete_survey_survey');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_survey_survey');
    }
}
