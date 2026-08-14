<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use App\Notifications\WebNotification;
use Carbon\Carbon;

class SendTimingNotifications extends Command
{
    protected $signature = 'app:send-timing-notifications';
    protected $description = '読書計画の期日に応じたタイミング通知を自動送信します';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // 三日前の通知
        $threeDaysBeforePlans = ReadingPlan::whereDate('target_date', $today->copy()->addDays(3))->get();
        foreach ($threeDaysBeforePlans as $plan) {
            $plan->user->notify(new WebNotification("読書計画の目標日まであと3日です", 'three_days_before'));
        }

        // 当日通知
        $onDueDatePlans = ReadingPlan::whereDate('taerget_date', $today)->get();
        foreach ($onDueDatePlans as $plan) {
            $plan->user->notify(new WebNotification("読了目標日になりました", 'on_due_date'));
        }

        // 三日後通知
        $threeDaysAfterPlans = ReadingPlan::whereDate('target_date', $today->copy()->subDays(3))
            ->where('status', '!=', 'completed')
            ->get();
        foreach ($threeDaysAfterPlans as $plan) {
            $plan->user->notify(new WebNotification("読了目標日から3日が経過しています", 'three_days_after'));
        }

        $this->info('読書計画の通知送信が完了しました');
    }
}
