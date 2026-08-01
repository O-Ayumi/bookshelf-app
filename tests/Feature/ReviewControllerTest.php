<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    /** @test */
    public function レビュー投稿時バリデーション通過するとレビューが保存される(): void
    {
        $reviewData = [
            'rating' => 5,
            'comment' => '素晴らしい本でした',
        ];

        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->book), $reviewData);

        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 5,
            'comment' => '素晴らしい本でした',
        ]);
    }

    /** @test */
    public function 未認証時はログイン画面にリダイレクトされる(): void
    {
        $reviewData = [
            'rating' => 5,
            'coment' => 'ゲストコメント',
        ];

        $response = $this->post(route('reviews.store', $this->book), $reviewData);

        $response->assertRedirect('/login');
        $this->assertDatabaseEmpty('reviews');
    }

    /** @test */
    public function 認証済みかつ本人のみ既存のratingとcommentが初期値として入った編集フォームが表示される(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.edit', $review));

        $response->assertStatus(200);
        $response->assertSee('3');
        $response->assertSee('元のコメント');
    }

    /** @test */
    public function アクセスが拒否される(): void
    {
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($otherUser)->get(route('reviews.edit', $review));

        $response->assertStatus(403);
    }

    /** @test */
    public function 本人がバリデーション通過時レビュー内容が更新される(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 2,
            'comment' => '古いコメント',
        ]);

        $updateData = [
            'rating' => 4,
            'comment' => '新しいコメント',
        ];

        $response = $this->actingAs($this->user)->put(route('reviews.update', $review), $updateData);

        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '新しいコメント',
        ]);
    }

    /** @test */
    public function 認証済みかつ本人のみレビューが削除され関連するいいねも削除される(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
        $review->likedByUsers()->attach($this->user->id);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function 本人以外は削除リクエストが拒否される(): void
    {
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(route('reviews.destroy', $review));

        $response->assertStatus(403);

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
