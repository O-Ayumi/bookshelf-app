<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証の時ログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function 認証時4種類の統計情報が表示される(): void
    {
        $user = User::factory()->create();

        $genreA = Genre::factory()->create(['name' => '小説']);
        $genreB = Genre::factory()->create(['name' => 'ビジネス']);

        $book1 = Book::factory()->create(['title' => '評価5の本']);
        $book2 = Book::factory()->create(['title' => '評価3の本']);
        $book3 = Book::factory()->create(['title' => '評価1の本']);

        $book1->genres()->attach($genreA);
        $book2->genres()->attach($genreA);
        $book3->genres()->attach($genreB);

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 3]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book3->id, 'rating' => 1]);

        $otherUser = User::factory()->create();
        Review::factory()->create(['user_id' => $otherUser->id, 'book_id' => $book1->id, 'rating' => 4]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) use ($book1, $genreA, $genreB) {
            $summaryValid = $stats['summary']['total_reviews'] === 3
                && $stats['summary']['books_read'] === 3
                && (float) $stats['summary']['average_rating'] >= 3;

            $distributionValid = $stats['rating_distribution'][5] === 1
                && $stats['rating_distribution'][4] === 0
                && $stats['rating_distribution'][3] === 1
                && $stats['rating_distribution'][2] === 0
                && $stats['rating_distribution'][1] === 1;

            $topBooksValid = count($stats['top_rated_books']) === 1
                && $stats['top_rated_books'][0]['id'] === $book1->id;

            $genreValid = count($stats['genre_ratings']) === 2
                && $stats['genre_ratings'][0]['id'] === $genreA->id
                && $stats['genre_ratings'][1]['id'] === $genreB->id;

            return $summaryValid && $distributionValid && $topBooksValid && $genreValid;
        });

        $response->assertSee('評価5の本');
        $response->assertSee(route('books.show', $book1->id));
        $response->assertSee(route('genres.show', $genreA->id));
    }

    /** @test */
    public function レビューが0件の時のメッセージが正しく表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('4星以上の書籍がありません');
        $response->assertSee('ジャンルが設定された書籍のレビューがありません');
    }
}
