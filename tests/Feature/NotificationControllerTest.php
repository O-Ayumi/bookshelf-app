<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証ユーザーは通知画面一覧を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertViewHas('notifications');
    }

    /** @test */
    public function 未認証時は通知一覧からログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 認証済みかつ認可ユーザーのみ通知を既読に出来る(): void
    {
        $user = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\WebNotification',
            'data' => ['title' => 'テスト通知', 'body' => '中身'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification));

        $response->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function 認可ユーザー以外が既読にしようとすると403が返る(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\WebNotification',
            'data' => ['title' => 'この操作の権限がありません', 'body' => '中身'],
        ]);

        $response = $this->actingAs($otherUser)->post(route('notifications.read', $notification));

        $response->assertStatus(403);
        $this->assertNull($notification->fresh()->read_at);
    }
}
