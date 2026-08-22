<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * レビューの登録
     *
     * @param  StoreReviewRequest  $request  バリデーション済のリクエスト
     * @param  Book  $book  特定の書籍ID
     * @return RedirectResponse　書籍詳細に遷移
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        try {
            $reviewData = $request->validated();
            $userId = auth()->id();

            DB::transaction(function () use ($userId, $book, $reviewData) {
                Review::updateOrCreate([
                    'user_id' => $userId,
                    'book_id' => $book->id,
                ], [
                    'rating' => $reviewData['rating'],
                    'comment' => $reviewData['comment'] ?? null,
                ]);
            });

            $book->unsetRelation('reviews');

            return back()->with('success', 'レビューを投稿しました');
        } catch (Exception $e) {
            Log::error('レビュー投稿失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '投稿に失敗しました');
        }
    }

    /**
     * レビューの編集画面の表示
     *
     * @param  Review  $review  特定のレビュー
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        $book = $review->book;

        return view('reviews.edit', compact('review', 'book'));
    }

    /**
     * レビューの編集
     *
     * @param  UpdateReviewRequest  $request  バリデーション済のリクエスト
     * @param  Review  $review  特定のレビュー
     * @return RedirectResponse　書籍詳細に遷移
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        try {
            $this->authorize('update', $review);

            $review->update($request->validated());

            return redirect()->route('books.show', $review->book_id)->with('success', 'レビューを更新しました');
        } catch (Exception $e) {
            Log::error('レビュー更新失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '更新に失敗しました');
        }
    }

    /**
     * レビューの削除
     *
     * @param  Review  $review  特定のレビュー
     * @return RedirectResponse　書籍詳細画面に遷移
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;

        try {
            $review->delete();

            return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました');
        } catch (Exception $e) {
            Log::error('レビュー削除失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '削除に失敗しました');
        }
    }
}
