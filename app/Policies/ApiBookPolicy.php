<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class ApiBookPolicy
{
    /**
     * ユーザーが特定の書籍情報を更新できるか判定する
     *
     * @param  User  $user  ログイン中のユーザーモデル
     * @param  Book  $book  対象の書籍モデル
     * @return bool 認可結果
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
