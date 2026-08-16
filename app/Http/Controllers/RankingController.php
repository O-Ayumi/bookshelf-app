<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

class RankingController extends Controller
{
    /**
     * ランキング一覧画面の表示
     */
    public function index(): View
    {
        $rankedBooks = Book::has('reviews')->withAvg('reviews', 'rating')->withCount('reviews')->orderBy('reviews_avg_rating', 'desc')->take(10)->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
