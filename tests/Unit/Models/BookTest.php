<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** @test */
    public function 書籍はユーザーに属する(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($book, '指定ユーザーに紐づく書籍がありません');
        $this->assertInstanceOf(User::class, $book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    /** @test */
    public function 書籍は複数のジャンルを持つ(): void
    {
        $book = Book::has('genres')->first();

        $this->assertInstanceOf(Collection::class, $book->genres);
        $this->assertInstanceOf(Genre::class, $book->genres->first());
    }

    /** @test */
    public function 書籍は複数のお気に入りしたユーザーを持つ(): void
    {
        $book = Book::has('favoritedByUsers')->first();

        $this->assertInstanceOf(Collection::class, $book->favoritedByUsers);
        $this->assertInstanceOf(User::class, $book->favoritedByUsers->first());
    }

    /** @test */
    public function 書籍は複数のレビューを持つ(): void
    {
        $book = Book::has('reviews')->first();

        $this->assertInstanceOf(Collection::class, $book->reviews);
        $this->assertInstanceOf(Review::class, $book->reviews->first());
    }

    /** @test */
    public function 各書籍に紐づくレビューの平均点が正しく計算できる(): void
    {
        $book = Book::has('reviews', '>=', 2)->withAvg('reviews', 'rating')->first();

        $expectedAverage = $book->reviews()->avg('rating');

        $this->assertEquals($expectedAverage, $book->reviews_avg_rating);
    }

    /** @test */
    public function レビューが1件もない書籍は平均点が_nul_lになる(): void
    {
        $book = Book::doesntHave('reviews')->first();

        if (! $book) {
            $user = User::first();
            $book = Book::create([
                'user_id' => $user->id,
                'title' => 'テスト用レビューなし書籍',
                'author' => 'テスト',
                'isbn' => 1111111111111,
                'published_date' => '2026-07-30',
            ]);
        }

        $bookWithAvg = Book::withAvg('reviews', 'rating')->find($book->id);

        $this->assertNull($bookWithAvg->reviews_avg_rating);
    }

    /** @test */
    public function 書籍を削除すると紐づいたレビュー、お気に入り、ジャンルも削除される(): void
    {
        $book = Book::has('reviews')->has('genres')->has('favoritedByUsers')->first();

        $bookId = $book->id;
        $reviewIds = $book->reviews->pluck('id');

        $book->delete();

        $this->assertDatabaseMissing('books', ['id' => $bookId]);

        foreach ($reviewIds as $id) {
            $this->assertDatabaseMissing('reviews', ['id' => $id]);
        }

        $this->assertDatabaseMissing('book_genre', ['book_id' => $bookId]);
        $this->assertDatabaseMissing('favorites', ['book_id' => $bookId]);
    }

    /** @test */
    public function 書籍は複数の読書計画を持つ(): void
    {
        $book = Book::has('readingPlans')->first();

        $this->assertInstanceOf(Collection::class, $book->readingPlans);
        $this->assertInstanceOf(ReadingPlan::class, $book->readingPlans->first());
    }
}
