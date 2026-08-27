<?php

namespace Tests\Unit\Models;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** @test */
    public function 読書計画はユーザーに属する(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $plan->user);
        $this->assertEquals($user->id, $plan->user->id);
    }

    /** @test */
    public function 読書計画は書籍に属する(): void
    {
        $book = Book::factory()->create();
        $plan = ReadingPlan::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $plan->book);
        $this->assertEquals($book->id, $plan->book->id);
    }

    /** @test */
    public function statusが_enumとしてキャストされる(): void
    {
        $plan = ReadingPlan::factory()->create(['status' => ReadingPlanStatus::Reading]);

        $this->assertInstanceOf(ReadingPlanStatus::class, $plan->status);
        $this->assertEquals(ReadingPlanStatus::Reading, $plan->status);
    }
}
