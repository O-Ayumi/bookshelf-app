<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function jso_n形式で20件ごとの書籍一覧が返される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Book::factory()->count(21)->hasAttached($genre)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
    }

    /** @test */
    public function 検索・絞り込み条件に応じて正しくデータがフィルタリングされる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $testBooks = Book::factory()->count(3)->hasAttached($genre)->create([
            'user_id' => $user->id,
            'author' => 'test',
        ]);
        $otherBooks = Book::factory()->count(3)->hasAttached($genre)->create([
            'user_id' => $user->id,
            'author' => 'other',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=test');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function 一覧が_jso_n構造になっている(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Book::factory()->hasAttached($genre)->create([
            'user_id' => $user->id,
            'title' => 'テストタイトル',
            'author' => 'テスト',
            'isbn' => '1111111111111',
            'published_date' => '2026-08-05',
            'description' => '',
            'image_url' => '',
        ]);

        $response = $this->getJson('api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'genres' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function 各書籍のデータにジャンル情報、平均評価、レビュー件数が含まれる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->hasAttached($genre)->hasReviews(1)->create(['user_id' => $user->id]);

        $response = $this->getJson('api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'genres',
                    'average_rating',
                    'review_count',
                ],
            ],
        ]);
    }

    /** @test */
    public function 指定した書籍の詳細が_jso_n形式で返される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->hasAttached($genre)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
            ],
        ]);
    }

    /** @test */
    public function 一覧で存在しない書籍_i_dを指定したとき404が返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->hasAttached($genre)->create([
            'user_id' => $user->id,
        ]);
        $nonExistenId = $book->id + 99999;

        $response = $this->getJson("api/v1/books/{$nonExistenId}");

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }

    /** @test */
    public function バリデーション通過時書籍が登録され成功レスポンスが返される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $data = [
            'title' => 'test',
            'author' => 'testAuthor',
            'isbn' => '1111111111111',
            'published_date' => '2026-08-05',
            'description' => '',
            'image_url' => '',
            'genres' => [$genre->id],
        ];

        // ログイン用のSanctum認証は書かずテスト内でのみトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('api/v1/books', $data);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'test');
        $response->assertJsonPath('data.author', 'testAuthor');
        $response->assertJsonPath('data.isbn', '1111111111111');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'test',
            'isbn' => '1111111111111',
        ]);
    }

    /** @test */
    public function バリデーション失敗時422が返される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $data = [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_date' => '',
            'description' => '',
            'image_url' => '',
            'genres' => '',
        ];

        $response = $this->actingAs($user)->postJson('api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);
    }

    /** @test */
    public function バリデーション通過時書籍が更新され成功レスポンスが返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->hasAttached($genre)->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => '更新',
            'author' => '更新後',
            'isbn' => '2222222222222',
            'published_date' => '2026-08-05',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("api/v1/books/{$book->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', '更新');
        $response->assertJsonPath('data.author', '更新後');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新',
        ]);
    }

    /** @test */
    public function 更新時存在しない書籍_i_dを指定したとき404が返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => '更新',
            'author' => '更新後',
            'isbn' => '2222222222222',
            'published_date' => '2026-08-05',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson('api/v1/books/99999', $data);

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }

    /** @test */
    public function 書籍が正常に削除され関連データも適切に処理され成功レスポンスが返される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // ログイン用のSanctum認証は書かずテスト内でのみトークンを発行
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("api/v1/books/{$book->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /** @test */
    public function 削除時存在しない_i_dを指定した場合404が返される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->hasAttached($genre)->create([
            'user_id' => $user->id,
        ]);
        $nonExistenId = $book->id + 99999;

        $response = $this->actingAs($user)->deleteJson("api/v1/books/{$nonExistenId}");

        $response->assertStatus(404);
    }

    /** @test */
    public function 未ログイン状態で書籍登録した場合401が返される(): void
    {
        $response = $this->postJson('api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function 未ログイン状態で書籍更新した場合401が返される(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新タイトル',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function 未ログイン状態で書籍削除した場合401が返される(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function 書き込み系操作の認可エラー時403が返される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $putResponse = $this->actingAs($otherUser)->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新の認可エラー',
            'author' => $book->author,
            'isbn' => '9999999999999',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);
        $putResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($otherUser)->deleteJson("/api/v1/books/{$book->id}");
        $deleteResponse->assertStatus(403);
    }
}
