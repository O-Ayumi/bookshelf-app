<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** @test */
    public function ユーザーは複数の書籍を有する(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(11)->create([
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($user, 'ユーザーデータが存在しません');

        $this->assertInstanceOf(Collection::class, $user->books);
        $this->assertCount(11, $user->books);
        $this->assertInstanceOf(Book::class, $user->books->first());
    }

    /** @test */
    public function ユーザーは複数のレビューを持つ(): void
    {
        $user = User::has('reviews')->first();

        $this->assertInstanceOf(Collection::class, $user->reviews);
        $this->assertInstanceOf(Review::class, $user->reviews->first());
    }

    /** @test */
    public function ユーザーは複数のお気に入り書籍を持つ(): void
    {
        $user = User::has('favoriteBooks')->first();

        $this->assertInstanceOf(Collection::class, $user->favoriteBooks);
        $this->assertInstanceOf(Book::class, $user->favoriteBooks->first());
    }

    /** @test */
    public function ユーザーは複数のいいねしたレビューを持つ(): void
    {
        $user = User::has('likedReviews')->first();

        $this->assertInstanceOf(Collection::class, $user->likedreviews);
        $this->assertInstanceOf(Review::class, $user->likedReviews->first());
    }

    /** @test */
    public function ユーザーがお気に入り登録した書籍一覧が取得できる(): void
    {
        $user = User::has('favoriteBooks')->first();

        $this->assertInstanceOf(Collection::class, $user->favoriteBooks);
        $this->assertInstanceOf(Book::class, $user->favoriteBooks->first());
    }

    /** @test */
    public function 自分が投稿したレビューが正しく取得できる(): void
    {
        $user = User::has('reviews')->first();

        $this->assertInstanceOf(Collection::class, $user->reviews);
        $this->assertEquals($user->id, $user->reviews->first()->user_id);
    }

    /** @test */
    public function ユーザーは複数の読書計画を持つ(): void
    {
        $user = User::has('readingPlans')->first();

        $this->assertInstanceOf(Collection::class, $user->readingPlans);
        $this->assertInstanceOf(ReadingPlan::class, $user->readingPlans->first());
    }
}
