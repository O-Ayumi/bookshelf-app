<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** @test */
    public function レビューは特定の書籍とユーザーに属する(): void
    {
        $review = Review::first();

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($review->book_id, $review->book->id);
        $this->assertEquals($review->user_id, $review->user->id);
    }

    /** @test */
    public function レビューは複数のいいねを持つ(): void
    {
        $review = Review::has('likedByUsers')->first();

        $this->assertInstanceOf(Collection::class, $review->likedByUsers);
        $this->assertInstanceOf(User::class, $review->likedbyUsers->first());
    }

    /** @test */
    public function レビューに紐づいた書籍とユーザーが取得できる(): void
    {
        $review = Review::first();

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertInstanceOf(User::class, $review->user);
    }

    /** @test */
    public function レビューを削除した際に紐づいたいいねも削除される(): void
    {
        $review = Review::has('likedByUsers')->first();
        $reviewId = $review->id;

        $review->delete();

        $this->assertDatabaseMissing('review_likes', ['review_id' => $reviewId]);
    }
}
