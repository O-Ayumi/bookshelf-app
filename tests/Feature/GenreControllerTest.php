<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.index'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 各ジャンルに紐づく書籍数が付いた状態で一覧が表示される(): void
    {
        $user = User::factory()->create();
        $genreA = Genre::factory()->create(['name' => '小説']);
        $genreB = Genre::factory()->create(['name' => 'ビジネス']);
        $books = Book::factory()->count(2)->create();

        $genreA->books()->attach($books->pluck('id'));

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertViewHas('genres', function ($genres) {
            $firstGenre = $genres->firstWhere('name', '小説');
            $secondGenre = $genres->firstWhere('name', 'ビジネス');

            return $firstGenre->books_count === 2 && $secondGenre->books_count === 0;
        });
    }

    /** @test */
    public function ジャンル名入力フォームが表示されバリデーション通過で登録される(): void
    {
        $user = User::factory()->create();
        $data = ['name' => '小説'];

        $response = $this->actingAs($user)->post(route('genres.store'), $data);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['name' => '小説']);
    }

    /** @test */
    public function バリデーションエラー時失敗エラーが表示される(): void
    {
        $user = User::factory()->create();
        $data = ['name' => ''];

        $response = $this->actingAs($user)->post(route('genres.store'), $data);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseMissing('genres', ['name' => '']);
    }

    /** @test */
    public function 編集画面で現在のジャンル名が初期値として表示されバリデーション通過時は更新される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '古いジャンル名']);

        $response = $this->actingAs($user)->get(route('genres.edit', $genre));

        $response->assertStatus(200);
        $response->assertSee('古いジャンル名');

        $updatedData = ['name' => '新しいジャンル名'];

        $response = $this->actingAs($user)->put(route('genres.update', $genre), $updatedData);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '新しいジャンル名']);
    }

    /** @test */
    public function バリデーションエラー時は失敗エラーが表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '古いジャンル名']);
        $data = ['name' => ''];

        $response = $this->actingAs($user)->put(route('genres.update', $genre), $data);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function 書籍の紐づけがある場合削除リクエストが拒否されエラーメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $genre->books()->attach($book->id);

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /** @test */
    public function 書籍の紐づけがなければ正常に削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }
}
