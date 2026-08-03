<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証時未いいねのレビューならいいね追加、いいね済のレビューならいいね解除される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->from(route('books.show', $review->book_id ?? 1))->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book_id ?? 1));
    }

    /** @test */
    public function 未認証時にいいね処理しようとするとログイン画面にリダイレクトされる(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect('/login');
    }
}
