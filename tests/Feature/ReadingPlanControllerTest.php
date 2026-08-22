<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトされる(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 認証時読書計画を一覧表示し状態による絞り込みができる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $bookA = Book::factory()->create(['user_id' => $user->id]);
        $bookB = Book::factory()->create(['user_id' => $otherUser->id]);
        $bookC = Book::factory()->create();

        $planInProgress = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookA->id,
            'status' => ReadingPlanStatus::Reading,
            'target_date' => now()->addDays(7),
        ]);

        $planCompleted = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookC->id,
            'status' => ReadingPlanStatus::Completed,
            'target_date' => now()->subDays(1),
        ]);

        $planOther = ReadingPlan::create([
            'user_id' => $otherUser->id,
            'book_id' => $bookB->id,
            'status' => ReadingPlanStatus::Reading,
            'target_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee($bookA->title);
        $response->assertSee($bookC->title);
        $response->assertDontSee($bookB->title);

        $response = $this->actingAs($user)->get(route('reading-plans.index', [
            'status' => ReadingPlanStatus::Reading->value,
        ]));

        $response->assertStatus(200);
        $response->assertSee($bookA->title);
        $response->assertDontSee($bookC->title);
    }

    /** @test */
    public function 認証済かつ認可ユーザーのみ読了ボタンでステータスが更新される(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $plan->id));

        $response->assertRedirect(route('books.show', $plan->book_id));
        $response->assertSessionHas('success', '読了しました');
        $this->assertEquals(
            ReadingPlanStatus::Completed,
            $plan->fresh()->status
        );
    }

    /** @test */
    public function 認可ユーザー以外が読了ボタンを押すと403が返る(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($otherUser)->post(route('reading-plans.complete', $plan->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function 認証済かつ認可ユーザーのみ読書計画編集画面へ遷移できる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $plan->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function 認可ユーザー以外は編集画面へ遷移できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($otherUser)->get(route('reading-plans.edit', $plan->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function 認証済みかつ認可ユーザーのみ計画削除ができる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $plan->id));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を削除しました');
        $this->assertModelMissing($plan);
    }

    /** @test */
    public function 認可ユーザー以外は計画を削除できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($otherUser)->delete(route('reading-plans.destroy', $plan->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function 認証時読書計画作成画面に遷移し書籍プルダウンと期日入力フォームが表示される(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertViewHas('books');
        $response->assertSee('name="book_id"', false);
        $response->assertSee('name="target_date"', false);
    }

    /** @test */
    public function 登録ボタンを押すとバリデーション通過時に計画が登録される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $targetDate = now()->addDays(7)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('reading-plans.store', [
            'book_id' => $book->id,
            'target_date' => $targetDate,
        ]));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を作成しました');
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate.' 00:00:00',
            'status' => 'unread',
        ]);
    }

    /** @test */
    public function 計画登録でバリデーションエラー時エラーメッセージが返される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store', [
            'book_id' => $book->id,
            'target_date' => '',
        ]));

        $response->assertSessionHasErrors([
            'target_date' => '期日を設定してください',
        ]);
        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 計画更新で認証かつ認可済ユーザーがバリデーション通過したとき情報が更新される(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $targetDate = now()->addDays(7)->format('Y-m-d');

        $response = $this->actingAs($user)->put(
            route('reading-plans.update', ['reading_plan' => $plan->id]),
            [
                'book_id' => $plan->book_id,
                'target_date' => $targetDate,
            ]
        );

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を更新しました');
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'user_id' => $user->id,
            'book_id' => $plan->book_id,
            'target_date' => $targetDate.' 00:00:00',
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function 認可ユーザー以外が更新すると403が返される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($otherUser)->put(
            route('reading-plans.update', ['reading_plan' => $plan->id]),
            [
                'book_id' => $plan->book_id,
                'target_date' => now()->addDays(7)->format('Y-m-d'),
            ]
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function 更新でバリデーションエラー時エラーメッセージが返される(): void
    {
        $user = User::factory()->create();
        $originalDate = now()->subDays(3)->format('Y-m-d 00:00:00');
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => $originalDate,
        ]);

        $response = $this->actingAs($user)->put(
            route('reading-plans.update', ['reading_plan' => $plan->id]),
            [
                'book_id' => $plan->book_id,
                'target_date' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'target_date' => '期日を設定してください',
        ]);
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $originalDate,
        ]);
    }
}
