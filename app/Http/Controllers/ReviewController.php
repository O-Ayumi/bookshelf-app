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
            $reviewData['user_id'] = auth()->id();
            $reviewData['book_id'] = $book->id;

            Review::create($reviewData);

            return back()->with('success', 'レビューを投稿しました');
        } catch (Exception $e) {
            Log::error('レビュー投稿失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '投稿に失敗しました');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        try {
            $review->update($request->validated());

            return redirect()->route('books.show', $review->book_id)->with('success', 'レビューを更新しました');
        } catch (Exception $e) {
            Log::error('レビュー更新失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '更新に失敗しました');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book, Review $review)
    {
        try {
            $review->delete();

            return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました');
        } catch (Exception $e) {
            Log::error('レビュー削除失敗:'.$e->getMessage());

            return back()->withInput()->with('error', '削除に失敗しました');
        }
    }
}
