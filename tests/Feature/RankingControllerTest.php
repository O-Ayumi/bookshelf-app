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
    public function ログインなしでランキング一覧が平均評価の降順に表示できる(): void
    {
        $bookA = Book::factory()->create(['title' => '評価5の本']);
        $bookB = Book::factory()->create(['title' => '評価3の本']);
        $bookC = Book::factory()->create(['title' => '評価1の本']);

        $reviewA = Review::factory()->create([
            'book_id' => $bookA->id,
            'rating' => 5,
            'comment' => '読んでいて楽しい本でした。',
        ]);

        $reviewB = Review::factory()->create([
            'book_id' => $bookB->id,
            'rating' => 3,
            'comment' => '普通の本でした。',
        ]);

        $reviewC = Review::factory()->create([
            'book_id' => $bookC->id,
            'rating' => 1,
            'comment' => 'あまりおもしろくなかった。',
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $bookA->title,
            $bookB->title,
            $bookC->title,
        ]);
    }
}
