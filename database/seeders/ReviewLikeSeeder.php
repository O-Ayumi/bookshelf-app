<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($reviews as $review) {
            $likeCount = rand(0, 3);

            if ($likeCount === 0) {
                continue;
            }

            $eligibleUsers = $users->where('id', '!=', $review->user_id);

            $chosenUsers = $eligibleUsers->random(min($likeCount, $eligibleUsers->count()));

            foreach ($chosenUsers as $user) {
                $user->likedReviews()->syncWithoutDetaching([$review->id]);
            }
        }
    }
}
