<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションのスケジュールの定義
     *
     * @param  Schedule  $schedule  スケジュール管理インスタンス
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:send-timing-notifications')->everyMinute();
    }

    /**
     * アプリケーションのコマンド登録
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
