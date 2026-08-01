<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    /** @test */
    public function レビュー投稿時バリデーション通過するとレビューが保存される(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create();
    }
}
