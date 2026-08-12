<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReadingPlanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReadingPlan $readingPlan): Response
    {
        return $user->id === $readingPlan->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReadingPlan $readingPlan): Response
    {
        return $user_id === $readingPlan->user_id ? response::allow() : response::deny('この操作の権限がありません');
    }
}
