<?php

namespace App\Http\Controllers;

use App\Models\Book;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = auth()->user()->favoriteBooks()->with('genres')->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back()->with('success', 'お気に入りを更新しました');
    }
}
