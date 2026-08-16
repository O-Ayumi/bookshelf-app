<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookPolicy
{
    /**
     * ユーザーが特定の書籍情報を更新できるか判定する
     *
     * @param  User  $user  ログイン中のユーザーモデル
     * @param  Book  $book  対象の書籍モデル
     * @return Response 認可レスポンス
     */
    public function update(User $user, Book $book): Response
    {
        return $user->id === $book->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }

    /**
     * ユーザーが特定の書籍情報を削除できるか判定する
     *
     * @param  User  $user  ログイン中のユーザーモデル
     * @param  Book  $book  対象の書籍モデル
     * @return Response 認可レスポンス
     */
    public function delete(User $user, Book $book): Response
    {
        return $user->id === $book->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }
}
