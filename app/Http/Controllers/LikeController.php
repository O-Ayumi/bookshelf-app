<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\log;

class LikeController extends Controller
{
    /**
     * いいねしたレビューのトグル処理
     *
     * @param  Review  $review  特定のレビュー
     * @return RedirectResponse　いいねの追加と解除
     */
    public function toggle(Review $review): RedirectResponse
    {
        try {
            DB::transaction(function () use ($review) {
                auth()->user()->likedReviews()->toggle($review->id);
            });

            return back()->with('success', 'いいねを更新しました');

        } catch (Exception $e) {
            log::error('いいねトグル処理失敗:'.$e->getMessage());

            return back()->with('error', '処理に失敗しました。');
        }
    }
}
