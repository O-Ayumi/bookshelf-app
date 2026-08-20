<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューが一件もない書籍はランキングエリアに表示されない(): void
    {
        $this->withoutExceptionHandling();
        $bookA = Book::factory()->create(['title' => 'レビューがある本']);

        $review = Review::factory()->create([
            'book_id' => $bookA->id,
            'rating' => 5,
            'comment' => '読んでいて楽しい本でした。',
        ]);

        $bookB = Book::factory()->create(['title' => 'レビューなしの本']);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee($bookA->title);
        $response->assertDontSee($bookB->title);
    }

    /** @test */
    public function ログインなしでランキング一覧が表示できる(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
    }
}
