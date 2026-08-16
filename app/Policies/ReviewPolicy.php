<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    /**
     * ユーザーが特定のレビューを更新できるか判定する
     *
     * @param  User  $user  ログイン中のユーザーモデル
     * @param  Review  $review  対象のレビューモデル
     * @return Response 認可レスポンス
     */
    public function update(User $user, Review $review): Response
    {
        return $user->id === $review->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }

    /**
     * ユーザーが特定のレビューを削除できるか判定する
     *
     * @param  User  $user  ログイン中のユーザーモデル
     * @param  Review  $review  対象のレビューモデル
     * @return Response 認可レスポンス
     */
    public function delete(User $user, Review $review): Response
    {
        return $user->id === $review->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }
}
