<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\WebNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTimingNotifications extends Command
{
    /**
     * コマンドを実行するためのArtisan名
     *
     * @var string
     */
    protected $signature = 'app:send-timing-notifications';

    /**
     * コマンドの概要説明
     *
     * @var string
     */
    protected $description = '読書計画の期日に応じたタイミング通知を自動送信します';

    /**
     * コンソールコマンドの処理
     *
     * @return int コマンドの終了ステータス
     */
    public function handle(): int
    {
        $today = Carbon::today();

        // 期限を過ぎても読了していない計画を期限切れにする
        ReadingPlan::where('target_date', '<', $today)
            ->where('status', '!=', ReadingPlanStatus::Completed->value)
            ->update(['status' => ReadingPlanStatus::Expired->value]);

        $this->info('読書計画の通知送信が完了しました');

        // 三日前の通知
        $threeDaysBeforePlans = ReadingPlan::whereDate('target_date', $today->copy()->addDays(3))->get();
        foreach ($threeDaysBeforePlans as $plan) {
            $plan->user->notify(new WebNotification('読書計画の目標日まであと3日です', 'three_days_before'));
        }

        // 当日通知
        $onDueDatePlans = ReadingPlan::whereDate('target_date', $today)->get();
        foreach ($onDueDatePlans as $plan) {
            $plan->user->notify(new WebNotification('読了目標日になりました', 'on_due_date'));
        }

        // 三日後通知
        $threeDaysAfterPlans = ReadingPlan::whereDate('target_date', $today->copy()->subDays(3))
            ->where('status', ReadingPlanStatus::Expired->value)
            ->get();
        foreach ($threeDaysAfterPlans as $plan) {
            $plan->user->notify(new WebNotification('読了目標日から3日が経過しています', 'expired'));
        }

        return Command::SUCCESS;
    }
}
