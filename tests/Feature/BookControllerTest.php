<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍一覧が10件ごとにページネーションされジャンル名も表示される(): void
    {
        Vite::spy();

        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '小説']);
        $books = Book::factory()->count(10)->create();
        $lastBook = Book::factory()->create([
            'title' => '非表示になる11件目の本',
            'created_at' => now()->subDay(),
        ]);

        foreach ($books->concat([$lastBook]) as $book) {
            $book->genres()->attach($genre->id);
        }

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertViewHas('books');
        $response->assertSee('小説');

        $response->assertDontSee($lastBook->title);
    }

    /** @test */
    public function 書籍詳細で基本情報とジャンルとレビュー一覧といいね数が表示される(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => '詳細テスト用',
            'author' => 'テスト著者',
            'isbn' => '1111111111111',
            'published_date' => '2026-07-31',
            'description' => '本の説明',
            'image_url' => 'http://example.com',
        ]);

        $genre = Genre::factory()->create(['name' => 'ビジネス']);
        $book->genres()->attach($genre->id);

        Review::factory()->create([
            'book_id' => $book->id,
            'comment' => '最高の読書体験でした。',
        ]);

        $response = $this->actingAs($user)->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('詳細テスト用');
        $response->assertSee('テスト著者');
        $response->assertSee('1111111111111');
        $response->assertSee('2026-07-31');
        $response->assertSee('本の説明');
        $response->assertSee('ビジネス');
        $response->assertSee('最高の読書体験でした。');
    }

    /** @test */
    public function 認証時に書籍登録画面へ遷移され、全ジャンルのチェックボックスが複数選択可能な形で存在する(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertViewIs('books.create');
        foreach ($genres as $genre) {
            $response->assertSee($genre->name);
        }
    }

    /** @test */
    public function 未認証時はログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(('/login'));
    }

    /** @test */
    public function バリデーション通過時は書籍が登録され選択したジャンルとの紐づけが作成される(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => '新規登録本',
            'author' => '新規著者',
            'isbn' => '1111111111111',
            'published_date' => '2026-07-31',
            'description' => '登録テスト',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->post(route('books.store'), $data);

        $latestBook = Book::latest('id')->first();
        $response->assertRedirect(route('books.show', $latestBook));

        $this->assertDatabaseHas('books', ['title' => '新規登録本', 'user_id' => $user->id]);

        $book = Book::where('title', '新規登録本')->first();
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    /** @test */
    public function 登録時のバリデーション失敗時はエラーメッセージが表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('books.create'))->post(route('books.store'), ['title' => '']);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors(['title']);
    }

    /** @test */
    public function 認証時かつ本人のみ編集フォームが表示される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->actingAs($otherUser)->get(route('books.edit', $book))->assertStatus(403);

        $this->actingAs($user)->get(route('books.edit', $book))->assertStatus(200);
    }

    /** @test */
    public function 本人がバリデーション通過した時書籍情報とジャンル紐づけが正常に更新される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id, 'title' => '古いタイトル']);

        $oldGenre = Genre::factory()->create();
        $newGenre = Genre::factory()->create();
        $book->genres()->attach($oldGenre->id);

        $updateData = [
            'title' => '新しいタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$newGenre->id],
        ];

        $response = $this->actingAs($user)->put(route('books.update', $book), $updateData);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '新しいタイトル']);

        $this->assertDatabaseMissing('book_genre', ['book_id' => $book->id, 'genre_id' => $oldGenre->id]);
        $this->assertDatabaseHas('book_genre', ['book_id' => $book->id, 'genre_id' => $newGenre->id]);
    }

    /** @test */
    public function 認証済かつ本人のみ書籍を削除でき、関連データも適切に削除され他ユーザーや未認証時は拒否される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create(['user_id' => $user->id]);
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->delete(route('books.destroy', $book))->assertRedirect('/login');
        $this->assertDatabaseHas('books', ['id' => $book->id]);

        $this->actingAs($otherUser)->delete(route('books.destroy', $book))->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $book->id]);

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));
        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('book_genre', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('favorites', ['book_id' => $book->id]);
    }

    /** @test */
    public function キーワードとジャンルによる書籍検索、絞り込み機能が正しく動作する(): void
    {
        $user = User::factory()->create();
        $genreA = Genre::factory()->create(['name' => '小説']);
        $genreB = Genre::factory()->create(['name' => 'ビジネス']);

        $matchedBook = Book::factory()->create([
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
        ]);

        $matchedBook->genres()->attach($genreA->id);

        $unmatchedBook = Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '山田太郎',
        ]);

        $unmatchedBook->genres()->attach($genreB->id);

        $response = $this->actingAs($user)->get(route('books.index', [
            'keyword' => '吾輩',
            'genre' => $genreA->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee($matchedBook->title);
        $response->assertDontSee($unmatchedBook->title);
    }

    /** @test */
    public function isb_n検索が正常に動作する(): void
    {
        $user = User::factory()->create();
        $isbn = '9782512548695';

        Http::fake([
            '*googleapis.com*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'モック化されたテスト本',
                            'authors' => ['テスト著者A', 'テスト著者B'],
                            'description' => '外部APIテスト用のモックデータ',
                            'publishedDate' => '2026-08-16',
                            'imageLinks' => [
                                'thumbnail' => 'http://example.com',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('books.fetch_isbn', ['isbn' => $isbn]));

        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'モック化されたテスト本',
            'author' => 'テスト著者A, テスト著者B',
            'description' => '外部APIテスト用のモックデータ',
            'published_date' => '2026-08-16',
            'image_url' => 'https://example.com',
        ]);
    }
}
