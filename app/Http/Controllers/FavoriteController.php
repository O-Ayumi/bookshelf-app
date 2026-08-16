<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧画面の表示
     *
     * @return View
     */
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->with('genres')->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * お気に入り書籍のトグル処理
     *
     * @param  Book  $book  特定の書籍
     * @return RedirectResponse　お気に入りの追加と解除
     */
    public function toggle(Book $book): RedirectResponse
    {
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back()->with('success', 'お気に入りを更新しました');
    }
}
