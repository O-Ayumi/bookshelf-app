<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            3 => [
                '入門編にはおすすめ。',
                'やや冗長だったが内容自体はためになった。',
                'もう少し具体例が欲しかったです。',
                '内容が専門的で上級者向けの印象でした。',
            ],
            4 => [
                'とても読みやすかったです。',
                '内容は面白かったですがかなりのボリュームです。',
                '全体的にクオリティが高い。',
                'ためになる内容ばかりだった。',
            ],
            5 => [
                '傑作です。何度も読み返したくなります。',
                '本当に素晴らしい本です。',
                '人生で一度は読んでほしい名作です。',
                'もっと早く出会いたかったと思わせる一冊。',
            ],
        ];

        $reviewCountPerBook = [3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2];

        foreach ($books as $index => $book) {
            $count = $reviewCountPerBook[$index];

            $existingUserIds = Review::where('book_id', $book->id)->pluck('user_id')->toArray();

            $eligibleUsers = $users->whereNotIn('id', $existingUserIds);

            $shuffledUsers = $eligibleUsers->shuffle()->take($count);

            foreach ($shuffledUsers as $user) {
                $rating = rand(3, 5);
                $commentPool = $comments[$rating];
                $comment = $commentPool[array_rand($commentPool)];

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);
            }
        }
    }
}
