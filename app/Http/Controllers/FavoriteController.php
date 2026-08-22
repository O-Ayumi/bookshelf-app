<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧画面の表示
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
        try {
            DB::transaction(function () use ($book) {
                auth()->user()->favoriteBooks()->toggle($book->id);
            });

            return back()->with('success', 'お気に入りを更新しました');
        } catch (Exception $e) {
            Log::error('お気に入りトグル処理失敗:'.$e->getMessage());

            return back()->with('error', '処理に失敗しました。');
        }
    }
}
