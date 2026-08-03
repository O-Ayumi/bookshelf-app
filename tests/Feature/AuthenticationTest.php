<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 新規登録時バリデーション通過時はユーザーが_d_bに登録され書籍一覧にリダイレクトされる(): void
    {
        $data = [
            'name' => 'testname',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $data);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertAuthenticated();
    }

    /** @test */
    public function バリデーションエラー時はエラーメッセージを伴って登録画面にリダイレクトされる(): void
    {
        $data = [
            'name' => '',
            'email' => '',
            'password' => '',
        ];

        $response = $this->from('/register')->post('/register', $data);

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);
    }

    /** @test */
    public function ログイン画面が表示できる(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    /** @test */
    public function 正しい認証情報でログインが完了し書籍一覧画面にリダイレクトされる(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function ログアウト時はセッションが破棄されログイン画面にリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
        $response->assertSessionHasAll([]);
    }
}
