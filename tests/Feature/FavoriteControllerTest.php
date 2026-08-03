<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証時未登録の書籍はお気に入り追加され登録済の時は解除される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->from(route('favorites.index'))->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('favorites.index'));
    }

    /** @test */
    public function 未認証時にトグル処理しようとするとログイン画面にリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 認証時ユーザーが登録したお気に入り書籍だけが10件ごとにページネーションで表示される(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $books = Book::factory()->count(10)->create([
            'created_at' => now()->subDays(2),
        ]);

        $lastBook = Book::factory()->create([
            'title' => '非表示になる11件目の本',
            'created_at' => now()->subDay(),
        ]);

        foreach ($books->concat([$lastBook]) as $book) {
            $book->favoritedByUsers()->attach($user->id);
        }

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertViewHas('books');
        $response->assertDontSee($lastBook->title);
    }

    /** @test */
    public function 未認証時に一覧表示しようとするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect('/login');
    }
}
