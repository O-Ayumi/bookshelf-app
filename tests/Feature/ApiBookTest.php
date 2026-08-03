<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function JSON形式で書籍一覧が返される(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
