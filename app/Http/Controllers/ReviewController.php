<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request, Book $book)
    {
        try {
            $reviewData = $request->validated();
            $userId = auth()->id();

            Review::updateOrCreate([
                'user_id' => $userId,
                'book_id' => $book->id,
            ], [
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'] ?? null,
            ]);

            $book->unsetRelation('reviews');

            return back()->with('success', 'レビューを投稿しました');
        } catch (Exception $e) {
            Log::error('レビュー投稿失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '投稿に失敗しました');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        $book = $review->book;

        return view('reviews.edit', compact('review', 'book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        try {
            $this->authorize('update', $review);

            $review->update($request->validated());

            return redirect()->route('books.show', $review->book_id)->with('success', 'レビューを更新しました');
        } catch (Exception $e) {
            Log::error('レビュー更新失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '更新に失敗しました');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        try {
            $this->authorize('delete', $review);

            $review->delete();

            return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました');
        } catch (Exception $e) {
            Log::error('レビュー削除失敗:' . $e->getMessage());

            return back()->withInput()->with('error', '削除に失敗しました');
        }
    }
}
