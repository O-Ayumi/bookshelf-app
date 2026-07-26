<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Book $book): Response
    {
        return $user->id === $book->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Book $book): Response
    {
        return $user->id === $book->user_id ? Response::allow() : Response::deny('この操作の権限がありません');
    }
}
