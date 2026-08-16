<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

/**
 * いいねしたレビューのトグル処理
 *
 * @param  Review  $review  特定のレビュー
 * @return RedirectResponse　いいねの追加と解除
 */
class LikeController extends Controller
{
    public function toggle(Review $review): RedirectResponse
    {
        auth()->user()->likedReviews()->toggle($review->id);

        return back()->with('success', 'いいねを更新しました');
    }
}
