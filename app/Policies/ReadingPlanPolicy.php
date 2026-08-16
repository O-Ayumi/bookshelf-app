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
     * ユーザーが特定の読書計画を更新できるか判定する
     *
     * @param  User  $user  ログイン中のユーザーモデル
     * @param  ReadingPlan  $readingPlan  対象の読書計画モデル
     * @return Response 認可レスポンス
     */
    public function update(User $user, ReadingPlan $readingPlan): Response
    {
        return $user->id === $readingPlan->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }

    /**
     * ユーザーが特定の読書計画を削除できるか判定する
     *
     * @param  User  $user  ログイン中のユーザーモデル
     * @param  ReadingPlan  $readingPlan  対象の読書計画モデル
     * @return Response 認可レスポンス
     */
    public function delete(User $user, ReadingPlan $readingPlan): Response
    {
        return $user->id === $readingPlan->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }
}
