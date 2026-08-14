<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yamada = User::where('name', 'like', '%山田太郎%')->first() ?? User::find(1);
        $suzuki = User::where('name', 'like', '%鈴木花子%')->first() ?? User::find(2);

        $books = Book::take(6)->get();

        // 山田太郎の3日前リマインダー
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[0]->id,
            'target_date' => Carbon::today()->addDays(3),
            'status' => 'in_progress',
        ]);

        // 山田太郎の当日リマインダー
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[1]->id,
            'target_date' => Carbon::today(),
            'status' => 'in_progress',
        ]);

        // 山田太郎の3日後リマインダー
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[2]->id,
            'target_date' => Carbon::today()->subDays(3),
            'status' => 'in_progress',
        ]);

        // 山田太郎：リマインダー対象外
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[3]->id,
            'target_date' => Carbon::today()->addDays(7),
            'status' => 'in_progress',
        ]);

        // 山田太郎：読了済
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[4]->id,
            'target_date' => Carbon::today()->subDays(10),
            'status' => 'completed',
            'completed_at' => Carbon::today()->subDays(5),
        ]);

        // 鈴木花子：他ユーザー認可テスト(403)用
        ReadingPlan::create([
            'user_id' => $suzuki->id,
            'book_id' => $books[5]->id,
            'target_date' => Carbon::today()->addDays(5),
            'status' => 'in_progress',
        ]);
    }
}
