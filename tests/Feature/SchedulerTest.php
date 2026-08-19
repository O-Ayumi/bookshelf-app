<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function スケジュールコマンドが正しく通知とステータス変更を行う(): void
    {
        $today = Carbon::today();
        Carbon::setTestNow($today);

        $user = User::factory()->create();

        $threeDaysBeforePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => $today->copy()->addDays(3)->format('Y-m-d 00:00:00'),
            'status' => ReadingPlanStatus::Unread->value,
        ]);

        $onDuedatePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => $today->format('Y-m-d 00:00:00'),
            'status' => ReadingPlanStatus::Reading->value,
        ]);

        $threeDaysAfterPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => $today->copy()->subDays(3)->format('Y-m-d 00:00:00'),
            'status' => ReadingPlanStatus::Reading->value,
        ]);

        $pastPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => $today->copy()->subDay()->format('Y-m-d 00:00:00'),
            'status' => ReadingPlanStatus::Unread->value,
        ]);

        $this->artisan('app:send-timing-notifications')->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $pastPlan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);

        $this->assertEquals(3, $user->unreadNotifications()->count());

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        Carbon::setTestNow();
    }
}
