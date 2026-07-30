<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** @test */
    public function ジャンルは複数の書籍に紐づく(): void
    {
        $genre = Genre::has('books')->first();
        $this->assertNotNull($genre);

        $this->assertInstanceOf(Collection::class, $genre->books);
        $this->assertGreaterThan(0, $genre->books->count());
        $this->assertInstanceOf(Book::class, $genre->books->first());
    }

    /** @test */
    public function ジャンルに紐づく書籍の総数が正しくカウントされる(): void
    {
        $genre = Genre::has('books')->first();

        $this->assertEquals($genre->books()->count(), $genre->books_count ?? $genre->books->count());
    }

    /** @test */
    public function 書籍が紐づいていないジャンルは削除できる(): void
    {
        $genre = Genre::doesntHave('books')->first();

        $genreId = $genre->id;
        $genre->delete();

        $this->assertDatabaseMissing('genres', ['id' => $genreId]);
    }

    /** @test */
    public function ジャンルと紐づいている書籍があると削除できない(): void
    {
        $genre = Genre::has('books')->first();

        $this->expectException(QueryException::class);

        $genre->delete();
    }
}
