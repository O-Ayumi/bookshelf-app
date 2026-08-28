<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\WebNotification;
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
        $today = now()->startOfDay();

        // 三日前の通知
        $threeDaysBeforePlans = ReadingPlan::whereDate('target_date', $today->copy()->addDays(3))
            ->where('status', ReadingPlanStatus::Reading->value)
            ->get();

        foreach ($threeDaysBeforePlans as $plan) {
            $bookTitle = $plan->book->title ?? '登録書籍';
            $plan->user->notify(new WebNotification("「{$bookTitle}」の読書計画の目標日まであと3日です", 'three_days_before'));
        }

        // 当日通知
        $onDueDatePlans = ReadingPlan::whereDate('target_date', $today)
            ->where('status', ReadingPlanStatus::Reading->value)
            ->get();

        foreach ($onDueDatePlans as $plan) {
            $bookTitle = $plan->book->title ?? '登録書籍';
            $plan->user->notify(new WebNotification("「{$bookTitle}」の読了目標日になりました", 'on_due_date'));
        }

        // 三日後通知(期日から三日以上経過かつまだ読了していないデータに通知)
        $threeDaysAfterPlans = ReadingPlan::whereDate('target_date', '=', $today->copy()->subDays(3))
            ->whereIn('status', [
                ReadingPlanStatus::Reading->value,
            ])
            ->get();

        foreach ($threeDaysAfterPlans as $plan) {
            $bookTitle = $plan->book->title ?? '登録書籍';
            $plan->user->notify(new WebNotification("「{$bookTitle}」の読了目標日から3日が経過しています", 'expired'));
        }

        ReadingPlan::where('target_date', '<', $today->toDateString())
            ->where('status', ReadingPlanStatus::Reading->value)
            ->update(['status' => ReadingPlanStatus::Expired->value]);

        return Command::SUCCESS;
    }
}
